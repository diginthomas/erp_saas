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

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\CustomField;
use Modules\Core\Updater\UpdatePatcher;
use Modules\Users\Models\User;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if (is_file(module_path('Core', 'Fields/ColorSwatches.php'))) {
            unlink(module_path('Core', 'Fields/ColorSwatches.php'));
        }

        if (is_file(module_path('Core', 'Fields/DropdownSelect.php'))) {
            unlink(module_path('Core', 'Fields/DropdownSelect.php'));
        }

        if (is_file(module_path('Core', 'Fields/MailEditor.php'))) {
            unlink(module_path('Core', 'Fields/MailEditor.php'));
        }

        if (is_file(module_path('Core', 'Fields/IntroductionField.php'))) {
            unlink(module_path('Core', 'Fields/IntroductionField.php'));
        }

        settings([
            '_last_cron_run' => settings('last_cron_run'),
            'last_cron_run' => null,
        ]);

        Innoclapps::registeredResources()
            ->whereInstanceOf(Tableable::class)
            ->each(function ($resource) {
                $request = app(ResourceRequest::class)->setResource($resource->name());
                $loggedInUser = Auth::user();
                foreach (User::get() as $user) {
                    $request->setUserResolver(fn () => $user);
                    Auth::setUser($user);
                    $table = $resource->resolveTable($request);

                    if (! $table->customizeable) {
                        continue;
                    }

                    if ($settings = $table->settings()->getCustomizedSettings()) {
                        foreach (['columns', 'order'] as $configKey) {
                            foreach ($settings[$configKey] as $key => $config) {
                                if (str_starts_with($config['attribute'], 'custom_field_')) {
                                    $fields = $resource->getFields()->filterCustomFields();

                                    $relatedField = $fields->first(function ($field) use ($config) {
                                        return Str::snake($field->customField->relationName, '_') === $config['attribute'];
                                    });

                                    if ($relatedField) {
                                        $settings[$configKey][$key]['attribute'] = $relatedField->attribute;
                                    }
                                }
                            }
                        }

                        $table->settings()->update($settings);
                    }
                }

                if ($loggedInUser && $loggedInUser->isNot(Auth::user())) {
                    Auth::setUser($loggedInUser);
                }
            });

        // Update old indexes with new.
        $uniqueCustomFields = CustomField::where('is_unique', true)->get();

        foreach ($uniqueCustomFields as $field) {
            $relatedModel = Innoclapps::resourceByName($field->resource_name)->newModel();

            $indexes = $this->getColumnIndexes($relatedModel->getTable(), $field->field_id);

            foreach ($indexes as $index) {
                if ($index['unique'] === true) {
                    Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($index) {
                        $table->dropUnique($index['name']);
                    });

                    Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($field) {
                        $table->unique($field->field_id, $field->uniqueIndexName());
                    });
                }
            }
        }
    }

    public function shouldRun(): bool
    {
        return is_file(module_path('Core', 'Fields/ColorSwatches.php')) ||
            is_file(module_path('Core', 'Fields/DropdownSelect.php')) ||
            is_file(module_path('Core', 'Fields/IntroductionField.php')) ||
            is_file(module_path('Core', 'Fields/MailEditor.php'));
    }
};
