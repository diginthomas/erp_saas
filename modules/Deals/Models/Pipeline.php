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

namespace Modules\Deals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;
use Modules\Core\Concerns\UserOrderable;
use Modules\Core\Contracts\Primaryable;
use Modules\Core\Models\CacheModel;
use Modules\Core\Resource\Resourceable;
use Modules\Core\Support\VisibilityGroup\HasVisibilityGroups;
use Modules\Core\Support\VisibilityGroup\RestrictsModelVisibility;
use Modules\Deals\Database\Factories\PipelineFactory;

class Pipeline extends CacheModel implements HasVisibilityGroups, Primaryable
{
    use HasFactory,
        Resourceable,
        RestrictsModelVisibility,
        UserOrderable;

    /**
     * The flag that indicates it's the primary pipeline
     */
    const PRIMARY_FLAG = 'default';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (Pipeline $model) {
            if ($model->isPrimary()) {
                abort(409, __('deals::deal.pipeline.delete_primary_warning'));
            } elseif ($model->deals()->count() > 0) {
                abort(409, __('deals::deal.pipeline.delete_usage_warning_deals'));
            }

            // We must delete the trashed deals when the pipeline is deleted
            // as we don't have any option to do with the deal except to associate
            // it with other pipeline (if found) but won't be accurate.
            $model->deals()->onlyTrashed()->get()->each(function (Deal $deal) {
                $deal->forceDelete();
            });

            $model->stages()->delete();
        });
    }

    /**
     * A pipeline has many deals
     */
    public function deals(): HasMany
    {
        return $this->hasMany(\Modules\Deals\Models\Deal::class);
    }

    /**
     * A pipeline has many stages
     */
    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class);
    }

    /**
     * Check whether the pipeline is the primary one
     */
    public function isPrimary(): bool
    {
        return $this->flag === static::PRIMARY_FLAG;
    }

    /**
     * Find the primary pipeline.
     */
    public static function findPrimary(): Pipeline
    {
        return static::where('flag', static::PRIMARY_FLAG)->first();
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    public function name(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.pipeline.'.$attributes['id'];

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
        $query->with([
            'stages',
            'visibilityGroup',
            'userOrder',
        ]);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PipelineFactory
    {
        return PipelineFactory::new();
    }
}
