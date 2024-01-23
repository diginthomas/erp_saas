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

namespace Modules\Users\Resourceables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Core\Contracts\Resources\HasOperations;
use Modules\Core\Contracts\Resources\Tableable;
use Modules\Core\Fields\BelongsToMany;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\ID;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\Timezone;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Events\ResourceRecordCreated;
use Modules\Core\Resource\Events\ResourceRecordUpdated;
use Modules\Core\Resource\Resource;
use Modules\Core\Rules\UniqueResourceRule;
use Modules\Core\Rules\ValidLocaleRule;
use Modules\Core\Rules\ValidTimezoneCheckRule;
use Modules\Core\Settings\SettingsMenuItem;
use Modules\Core\Table\Column;
use Modules\Core\Table\Table;
use Modules\Users\Http\Resources\UserResource;
use Modules\Users\Services\UserService;

class User extends Resource implements HasOperations, Tableable
{
    /**
     * The column the records should be default ordered by when retrieving
     */
    public static string $orderBy = 'name';

    /**
     * The model the resource is related to
     */
    public static string $model = 'Modules\Users\Models\User';

    /**
     * Provide the resource table class instance.
     */
    public function table(Builder $query, ResourceRequest $request): Table
    {
        $table = new Table($query, $request);

        return $table->select(['avatar', 'super_admin'])
            ->appends(['avatar_url'])
            ->with(['teams', 'managedTeams'])
            ->customizeable()
            ->orderBy(static::$orderBy, static::$orderByDir);
    }

    /**
     * Get the resource search columns.
     */
    public function searchableColumns(): array
    {
        return ['name' => 'like', 'email'];
    }

    /**
     * Get the fields for index.
     */
    public function fieldsForIndex(): FieldsCollection
    {
        return (new FieldsCollection([
            Text::make('name', __('users::user.name'))
                ->tapIndexColumn(fn (Column $column) => $column
                    ->width('300px')
                    ->route(['name' => 'edit-user', 'params' => ['id' => '{id}']])
                    ->primary()),

            ID::make(),

            Text::make('email', __('users::user.email'))->tapIndexColumn(
                fn (Column $column) => $column->link('mailto:{email}')
            ),

            BelongsToMany::make('roles', __('core::role.roles'))
                ->labelKey('name')
                ->displayAsPills()
                ->hidden(),

            BelongsToMany::make('teams', __('users::team.teams'))
                ->labelKey('name')
                ->displayAsPills()
                ->hidden(),

            Timezone::make('timezone', __('core::app.timezone'))->hidden(),

            Boolean::make('super_admin', __('users::user.super_admin')),

            Boolean::make('access_api', __('core::api.access'))->hidden(),

            DateTime::make('created_at', __('core::app.created_at'))->hidden(),

            DateTime::make('updated_at', __('core::app.updated_at'))->hidden(),
        ]))->disableInlineEdit();
    }

    /**
     * Create resource record.
     */
    public function create(Model $model, ResourceRequest $request): Model
    {
        $user = (new UserService)->create($model, $request->all());

        ResourceRecordCreated::dispatch($user, $this);

        return $user;
    }

    /**
     * Update resource record.
     */
    public function update(Model $model, ResourceRequest $request): Model
    {
        $user = (new UserService)->update($model, $request->all());

        ResourceRecordUpdated::dispatch($user, $this);

        return $user;
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): string
    {
        return UserResource::class;
    }

    /**
     * Get the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'password' => [
                $request->route('resourceId') ? 'nullable' : 'required', 'confirmed', 'min:6',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                UniqueResourceRule::make(static::$model),
            ],
            'locale' => ['nullable', new ValidLocaleRule],
            'timezone' => ['required', 'string', new ValidTimezoneCheckRule],
            'time_format' => ['required', 'string', Rule::in(config('core.time_formats'))],
            'date_format' => ['required', 'string', Rule::in(config('core.date_formats'))],
        ];
    }

    /**
     * Provides the resource available actions
     */
    public function actions(ResourceRequest $request): array
    {
        return [
            (new \Modules\Users\Actions\UserDelete)->canSeeWhen('is-super-admin'),
        ];
    }

    /**
     * Register the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [
            SettingsMenuItem::make(__('users::user.users'), '/settings/users', 'Users')->order(41),
        ];
    }
}
