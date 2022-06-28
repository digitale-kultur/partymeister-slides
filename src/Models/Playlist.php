<?php

namespace Partymeister\Slides\Models;

use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;

/**
 * Partymeister\Slides\Models\Playlist
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $is_competition
 * @property int $is_prizegiving
 * @property int|null $competition_id
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read mixed $item_count
 * @property-read Collection|PlaylistItem[] $items
 * @property-read User $updater
 * @method static Builder|Playlist filteredBy(Filter $filter, $column)
 * @method static Builder|Playlist filteredByMultiple(Filter $filter)
 * @method static Builder|Playlist newModelQuery()
 * @method static Builder|Playlist newQuery()
 * @method static Builder|Playlist query()
 * @method static Builder|Playlist search($q, $full_text = false)
 * @method static Builder|Playlist whereCreatedAt($value)
 * @method static Builder|Playlist whereCreatedBy($value)
 * @method static Builder|Playlist whereDeletedBy($value)
 * @method static Builder|Playlist whereId($value)
 * @method static Builder|Playlist whereIsCompetition($value)
 * @method static Builder|Playlist whereName($value)
 * @method static Builder|Playlist whereType($value)
 * @method static Builder|Playlist whereUpdatedAt($value)
 * @method static Builder|Playlist whereUpdatedBy($value)
 * @mixin Eloquent
 */
class Playlist extends Model
{
    use Searchable;
    use Filterable;
    use Blameable, CreatedBy, UpdatedBy, DeletedBy;

    /**
     * Columns for the Blameable trait
     *
     * @var array
     */
    protected $blameable = ['created', 'updated', 'deleted'];

    /**
     * Searchable columns for the searchable trait
     *
     * @var array
     */
    protected $searchableColumns = [
        'name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'is_competition',
	    'is_prizegiving',
	    'competition_id'
    ];

    /**
     * @return int
     */
    /**
     * @return int
     */
    public function getItemCountAttribute()
    {
        return $this->items()
                    ->count();
    }


    /**
     * @return HasMany
     */
    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(PlaylistItem::class);
    }
}
