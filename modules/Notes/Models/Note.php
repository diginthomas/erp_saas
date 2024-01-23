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

namespace Modules\Notes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Comments\Concerns\HasComments;
use Modules\Core\Concerns\LazyTouchesViaPivot;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resourceable;
use Modules\Core\Support\Media\HasAttributesWithPendingMedia;
use Modules\Core\Support\Timeline\Timelineable;
use Modules\Notes\Database\Factories\NoteFactory;

class Note extends Model
{
    use HasAttributesWithPendingMedia,
        HasComments,
        HasFactory,
        LazyTouchesViaPivot,
        Resourceable,
        Timelineable;

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
        'user_id' => 'int',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::creating(function (Note $model) {
            $model->user_id = $model->user_id ?: auth()->id();
        });

        static::deleting(function (Note $model) {
            $model->purge();
        });
    }

    /**
     * Get all of the contacts that are assigned this note.
     */
    public function contacts(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Contact::class, 'noteable');
    }

    /**
     * Get all of the companies that are assigned this note.
     */
    public function companies(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Contacts\Models\Company::class, 'noteable');
    }

    /**
     * Get all of the deals that are assigned this note.
     */
    public function deals(): MorphToMany
    {
        return $this->morphedByMany(\Modules\Deals\Models\Deal::class, 'noteable');
    }

    /**
     * Get the note owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the attributes that may contain pending media
     */
    public function attributesWithPendingMedia(): string
    {
        return 'body';
    }

    /**
     * Get the timeline component for front-end
     */
    public function getTimelineComponent(): string
    {
        return 'record-tab-timeline-note';
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->withCount('comments')->with('user');
    }

    /**
     * Provide the related pivot relationships to touch.
     */
    protected function relatedPivotRelationsToTouch(): array
    {
        return ['contacts', 'companies', 'deals'];
    }

    /**
     * Purge the note data.
     */
    public function purge(): void
    {
        foreach (['contacts', 'companies', 'deals'] as $relation) {
            $this->{$relation}()->withTrashed()->detach();
        }
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }
}
