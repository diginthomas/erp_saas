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

namespace Modules\Comments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\HasCreator;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\Model;
use Modules\Core\Support\Media\HasAttributesWithPendingMedia;
use Modules\Users\Mention\PendingMention;

class Comment extends Model
{
    use HasAttributesWithPendingMedia,
        HasCreator,
        HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_by' => 'int',
    ];

    /**
     * Get the parent commentable model
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the attributes that may contain pending media
     */
    public function attributesWithPendingMedia(): string
    {
        return 'body';
    }

    /**
     * Notify the mentioned users for the given mention.
     *
     * @param  string|null  $viaResource
     * @param  int|null  $viaResourceId
     * @return void
     */
    public function notifyMentionedUsers(PendingMention $mention, $viaResource = null, $viaResourceId = null): static
    {
        $isViaResource = $viaResource && $viaResourceId;

        $intermediate = $isViaResource ?
            Innoclapps::resourceByName($viaResource)->newModel()->find($viaResourceId) :
            $this->commentable;

        $mention->setUrl($intermediate->path)->withUrlQueryParameter([
            ...[
                'comment_id' => $this->getKey(),
            ],
            ...array_filter([
                'section' => $isViaResource ? $this->commentable->resource()->name() : null,
                'resourceId' => $isViaResource ? $this->commentable->getKey() : null,
            ]),
        ])->notify();

        return $this;
    }
}
