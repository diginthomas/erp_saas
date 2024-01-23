<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Modules\WebForms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Source;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\User;
use Modules\Core\Models\Changelog;
use Modules\Users\Models\User as UserModel;
use Modules\WebForms\Http\Requests\WebFormRequest;
use Modules\WebForms\Mail\WebFormSubmitted;
use Modules\WebForms\Models\WebForm;
use Plank\Mediable\Exceptions\MediaUploadException;
use Plank\Mediable\Facades\MediaUploader;

class FormSubmissionService
{
    /**
     * Process the web form submission.
     */
    public function submit(WebFormRequest $request): void
    {
        ChangeLogger::disable();
        $webForm = $request->webForm();

        $request->setResource('contacts');

        $firstNameField = $request->findField('first_name');
        $displayName = null;

        $phoneField = $request->findField('phones');
        $emailField = $request->findField('email');

        User::setAssigneer($webForm->creator);

        if (! $firstNameField) {
            $displayName = $emailField ?
            $request[$emailField->requestAttribute()] :
            $request[$phoneField->requestAttribute()][0]['number'] ?? 'Unknown';
        }

        $contact = $this->findDuplicateContact($request, $emailField, $phoneField);

        if ($contact) {
            // Track updated fields
            ChangeLogger::enable();
            $updateRequest = $request->asUpdateRequest($contact);
            $contact = $request->resource()->update($updateRequest->hydrateModel($contact), $request);
            ChangeLogger::disable();
        } else {
            $firstName = ! $firstNameField ? $displayName : $request[$firstNameField->requestAttribute()];

            $createRequest = $request->asCreateRequest();

            $contact = $request
                ->resource()
                ->create($createRequest->newHydratedModel([
                    'first_name' => $firstName,
                    'user_id' => $webForm->user_id,
                    'source_id' => $this->getSource()->getKey(),
                ]), $createRequest);
        }

        $this->handleResourceUploadedFiles($request, $contact);

        // Update the displayable fallback name with the actual contact display name
        // for example, if the form had first name and lastname fields, the full name will be used as fallback
        $displayName = $contact->display_name;

        // Handle the deal creation
        $deal = $this->handleDealFields($request, $displayName, $contact);

        $request->setResource('companies');

        if ($request->getFields()->isNotEmpty() || $webForm->getFileSectionsForResource('companies')->isNotEmpty()) {
            $company = $this->handleCompanyFields($request, $displayName, $deal, $contact);
            $this->handleResourceUploadedFiles($request, $company);
        }

        $changelog = (new FormSubmissionLogger(array_filter([
            'contacts' => $contact,
            'deals' => $deal,
            'companies' => $company ?? null,
        ]), $request))->log();

        $webForm->increment('total_submissions');

        $this->handleWebFormNotifications($webForm, $changelog);

        ChangeLogger::enable();
    }

    /**
     * Find duplicate company.
     */
    protected function findDuplicateCompany(WebFormRequest $request, string $companyName, ?string $companyEmail): ?Company
    {

        /** @var \Modules\Contacts\Resourceables\Company\Company */
        $resource = $request->resource();

        if ($company = $request->findRecordFromUniqueCustomFields()) {
            return $company;
        }

        if ($companyEmail && $company = $resource->findByEmail($companyEmail, $resource->newQuery())) {
            return $company;
        }

        return $resource->findByName($companyName, $resource->newQuery());
    }

    /**
     * Find duplicate contact.
     */
    protected function findDuplicateContact(WebFormRequest $request, ?Field $emailField, ?Field $phoneField): ?Contact
    {
        /** @var \Modules\Contacts\Resourceables\Contact\Contact */
        $resource = $request->resource();

        if ($contact = $request->findRecordFromUniqueCustomFields()) {
            return $contact;
        }

        if ($emailField && ! empty($email = $request[$emailField->requestAttribute()])) {
            if ($contact = $resource->findByEmail($email, $resource->newQuery())) {
                return $contact;
            }
        }

        if ($phoneField && ! empty($phones = $request[$phoneField->requestAttribute()])) {
            if ($contact = $resource->findByPhones($phones)) {
                if (! $contact->trashed()) {
                    return $contact;
                }
            }
        }

        return null;
    }

    /**
     * Handle the web form deal fields.
     *
     * @param  string  $fallbackName
     * @param  \Modules\Contacts\Models\Contact  $contact
     * @return \Modules\Deals\Models\Deal
     */
    protected function handleDealFields(WebFormRequest $request, $fallbackName, $contact)
    {
        $request->setResource('deals');

        $nameField = $request->findField('name');
        $name = $this->determineDealName($request, $fallbackName, $nameField);

        $deal = $request->resource()->newModel([
            'pipeline_id' => $request->webForm()->submit_data['pipeline_id'],
            'stage_id' => $request->webForm()->submit_data['stage_id'],
            'user_id' => $request->webForm()->user_id,
            'web_form_id' => $request->webForm()->id,
        ]);

        if (! $nameField) {
            $deal->fill(['name' => $name]);
        } else {
            $request[$nameField->requestAttribute()] = $name;
        }

        $createRequest = $request->asCreateRequest();

        $request->resource()->create(
            $createRequest->hydrateModel($deal),
            $createRequest
        );

        $deal->contacts()->attach($contact);

        $this->handleResourceUploadedFiles($request, $deal);

        return $deal;
    }

    /**
     * Determine the deal name.
     */
    protected function determineDealName(WebFormRequest $request, $fallbackName, ?Field $dealNameField)
    {
        $dealName = $dealNameField ?
            $dealNameField->attributeFromRequest(
                $request,
                $dealNameField->requestAttribute()
            ) :
            $fallbackName.' Deal';

        if (! empty($request->webForm()->title_prefix)) {
            $dealName = $request->webForm()->title_prefix.$dealName;
        }

        return $dealName;
    }

    /**
     * Handle the company fields.
     *
     * @param  string  $fallbackName
     * @param  \Modules\Deals\Models\Deal  $deal
     * @param  \Modules\Contacts\Models\Contact  $contact
     * @return \Modules\Contacts\Models\Company|null
     */
    protected function handleCompanyFields(WebFormRequest $request, $fallbackName, $deal, $contact)
    {
        $resource = $request->resource();
        $companyNameField = $request->findField('name');

        $companyName = ! $companyNameField ?
            $fallbackName.' Company' :
            $request[$companyNameField->requestAttribute()];

        if ($companyEmailField = $request->findField('email')) {
            $companyEmail = $request[$companyEmailField->requestAttribute()];
        }

        if ($company = $this->findDuplicateCompany(
            $request,
            $companyName,
            $companyEmail ?? null
        )) {
            // Track updated fields
            ChangeLogger::enable();
            $updateRequest = $request->asUpdateRequest($company);
            $resource->update($updateRequest->hydrateModel($company), $updateRequest);

            // It can be possible the contact to be already attached to the company e.q. in case the same form
            // is submitted twice, in this case, the company will exists as well the contact
            $company->contacts()->syncWithoutDetaching($contact);
            ChangeLogger::disable();
        } else {
            $createRequest = $request->asCreateRequest();

            $company = $resource->create($createRequest->newHydratedModel([
                $companyNameField?->attribute ?? 'name' => $companyName,
                'user_id' => $request->webForm()->user_id,
                'source_id' => $this->getSource()->getKey(),
            ]), $createRequest);

            $company->contacts()->attach($contact);
        }

        $company->deals()->attach($deal);

        return $company;
    }

    /**
     * Handle the resource uploaded files.
     *
     * @param  \Modules\WebForms\Http\Requests\WebFormRequest  $request
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    protected function handleResourceUploadedFiles($request, $model)
    {
        // Before this function is called, the resource must be set the resource
        // e.q. $request->setResource('companies') so the sections are properly determined

        $files = $request->webForm()->getFileSectionsForResource($request->resource()->name());

        $files->each(function (array $section) use ($request, $model) {
            foreach (Arr::wrap($request[$section['requestAttribute']]) as $uploadedFile) {
                // try {
                $media = MediaUploader::fromSource($uploadedFile)
                    ->toDirectory($model->getMediaDirectory())
                    ->upload();
                // } catch (MediaUploadException $e) {
                // $exception = $this->transformMediaUploadException($e);
                /*
                            return $this->response(
                                ['message' => $exception->getMessage()],
                                $exception->getStatusCode()
                            );*/
                //  }
                $model->attachMedia($media, $model->getMediaTags());
            }
        });
    }

    /**
     * Get the web form source.
     */
    protected function getSource(): Source
    {
        return Source::where('flag', 'web-form')->first();
    }

    /**
     * Handle the web form notification.
     */
    protected function handleWebFormNotifications(WebForm $form, Changelog $changelog): void
    {
        if (count($form->notifications) === 0) {
            return;
        }

        foreach ($this->getNotificationRecipients($form) as $recipient) {
            Mail::to($recipient)->send(
                new WebFormSubmitted($form, new FormSubmission($changelog))
            );
        }
    }

    /**
     * Get the notification recipients.
     */
    protected function getNotificationRecipients(WebForm $form): Collection
    {
        $users = UserModel::whereIn('email', $form->notifications)->get()->toBase();

        $usersEmails = $users->pluck('email')->all();

        if ($usersEmails != $form->notifications) {
            $nonUsersEmails = array_diff($form->notifications, $usersEmails);
        }

        return $users->merge($nonUsersEmails ?? []);
    }
}
