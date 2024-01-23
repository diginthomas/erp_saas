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

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Contracts\Primaryable;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;
use Modules\Core\Support\VisibilityGroup\HasVisibilityGroups;
use Modules\Core\Support\VisibilityGroup\RestrictsModelVisibility;
use Modules\Documents\Database\Factories\DocumentTypeFactory;

class DocumentType extends CacheModel implements HasVisibilityGroups, Primaryable
{
    use HasFactory,
        Resourceable,
        RestrictsModelVisibility;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'swatch_color',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (DocumentType $model) {
            if ($model->isPrimary()) {
                abort(409, __('documents::document.type.delete_primary_warning'));
            } elseif (DocumentType::getDefaultType() == $model->getKey()) {
                abort(409, __('documents::document.type.delete_is_default'));
            } elseif ($model->documents()->count() > 0) {
                abort(409, __('documents::document.type.delete_usage_warning'));
            }

            // We must delete the trashed documents when the type is deleted
            // as we don't have any option to do with the activity except to associate
            // it with other type (if found) but won't be accurate.
            $model->documents()->onlyTrashed()->get()->each(function (Document $document) {
                $document->forceDelete();
            });
        });
    }

    /**
     * Set the activity type
     */
    public static function setDefault(int $id): void
    {
        settings(['default_document_type' => $id]);
    }

    /**
     * Get the activity default type
     */
    public static function getDefaultType(): ?int
    {
        return settings('default_document_type');
    }

    /**
     * Get the type icon
     *
     * @todo add icons based on flags
     */
    public function icon(): Attribute
    {
        return Attribute::get(function () {
            return match (true) {
                default => 'DocumentText',
            };
        });
    }

    /**
     * A document type has many documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(\Modules\Documents\Models\Document::class);
    }

    /**
     * Check whether the document type is primary
     */
    public function isPrimary(): bool
    {
        return ! is_null($this->flag);
    }

    /**
     * Title attribute accessor
     *
     * Supports translation from language file
     */
    public function name(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.document_type.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->with('visibilityGroup');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DocumentTypeFactory
    {
        return DocumentTypeFactory::new();
    }
}
