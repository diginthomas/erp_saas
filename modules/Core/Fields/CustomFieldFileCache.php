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

namespace Modules\Core\Fields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldOption;

class CustomFieldFileCache
{
    protected static ?CustomFieldCollection $cache = null;

    public static function get(): CustomFieldCollection
    {
        if (! static::cached()) {
            static::store();
        }

        return static::$cache ??= static::toCollection(static::getFromFile());
    }

    public static function flush(): bool
    {
        if (! static::cached()) {
            return false;
        }

        if (File::delete(static::path())) {
            static::$cache = null;

            return true;
        }

        return false;
    }

    public static function refresh(): void
    {
        static::store();
    }

    public static function store(): void
    {
        $fields = static::load();

        File::replace(static::path(), json_encode($fields->toArray()));

        static::$cache = $fields;
    }

    public static function cached(): bool
    {
        return File::exists(static::path());
    }

    public static function toCollection(array $fields): CustomFieldCollection
    {
        $model = new CustomField;

        return $model->newCollection($fields)
            ->map(function (array $field) use ($model) {
                $options = static::optionsToCollection(Arr::pull($field, 'options'));

                return $model->newInstance($field, true)
                    ->forceFill(['id' => $field['id']])
                    ->setRelation('options', $options);
            });
    }

    protected static function optionsToCollection(array $options): Collection
    {
        $model = new CustomFieldOption;

        return $model->newCollection($options)
            ->map(function (array $option) use ($model) {
                return $model->newInstance($option, true)->forceFill(['id' => $option['id']]);
            });
    }

    public static function path(): string
    {
        return storage_path('.custom.fields.cache');
    }

    protected static function getFromFile(): array
    {
        return json_decode(File::get(static::path()), true);
    }

    protected static function load(): CustomFieldCollection
    {
        return CustomField::with('options')->get();
    }
}
