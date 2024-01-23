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

use Illuminate\Support\Str;
use Modules\Core\Models\Model;
use Plank\Mediable\Mediable;

/** @mixin \Modules\Core\Models\Model */
trait HasMedia
{
    use Mediable;

    /**
     * Boot HasMedia trait
     */
    protected static function bootHasMedia(): void
    {
        static::deleting(function (Model $model) {
            if ($model->isReallyDeleting()) {
                $model->purgeMedia();
            }
        });
    }

    /**
     * Purge the model media
     *
     * The function deletes the files and the storage folder when empty
     */
    public function purgeMedia(): void
    {
        $this->media()->get()->each->delete();
    }

    /**
     * Get the model media directory
     */
    public function getMediaDirectory(): string
    {
        $folder = Str::kebab(class_basename(get_called_class()));

        return config('core.media.directory').DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$this->id;
    }

    /**
     * Get the model media tag
     */
    public function getMediaTags(): array
    {
        return ['profile'];
    }
}
