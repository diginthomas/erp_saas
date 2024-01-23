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

namespace Modules\Core\Support\Media;

use Modules\Core\Models\Model;

/** @mixin \Modules\Core\Models\Model */
trait HasAttributesWithPendingMedia
{
    /**
     * Boot HasAttributesWithPendingMedia trait
     */
    protected static function bootHasAttributesWithPendingMedia(): void
    {
        static::updated(function (Model $model) {
            static::runMediaProcessor($model);
        });

        static::created(function (Model $model) {
            static::runMediaProcessor($model);
        });

        static::deleted(function (Model $model) {
            if ($model->isReallyDeleting()) {
                static::createMediaProcessor()->deleteAllViaModel(
                    $model,
                    $model->attributesWithPendingMedia()
                );
            }
        });
    }

    /**
     * Get the attributes that may contain pending media
     */
    abstract public function attributesWithPendingMedia(): array|string;

    /**
     * Run the editor media processor
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    protected static function runMediaProcessor($model)
    {
        static::createMediaProcessor()->processViaModel(
            $model,
            $model->attributesWithPendingMedia()
        );
    }

    /**
     * Create media processor
     */
    protected static function createMediaProcessor(): EditorPendingMediaProcessor
    {
        return new EditorPendingMediaProcessor();
    }
}
