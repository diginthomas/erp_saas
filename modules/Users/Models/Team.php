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

namespace Modules\Users\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\CacheModel;
use Modules\Core\Support\VisibilityGroup\VisibilityDependentable;
use Modules\Users\Database\Factories\TeamFactory;

class Team extends CacheModel
{
    use HasFactory,
        VisibilityDependentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'user_id',
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
        static::deleting(function (Team $model) {
            $model->purge();
        });
    }

    /**
     * Get the team manager
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the users that belong to the team
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps()->as('membership');
    }

    /**
     * Get all of the team's users including its manager
     */
    public function allUsers(): Collection
    {
        return $this->users->merge([$this->manager]);
    }

    /**
     * Scope a query to include only the teams the user belongs to.
     */
    public function scopeUserTeams(Builder $query, ?User $user = null): void
    {
        /** @var \Modules\Users\Models\User */
        $user = $user ?: Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $query->whereHas('users', function ($query) use ($user) {
            return $query->where('user_id', $user->getKey());
        })->orWhere('teams.user_id', $user->getKey());
    }

    /**
     * Purge the team data.
     */
    public function purge(): void
    {
        $this->users()->detach();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }
}
