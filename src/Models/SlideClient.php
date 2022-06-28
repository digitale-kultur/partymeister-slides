<?php

namespace Partymeister\Slides\Models;

use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Motor\Backend\Models\User;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use Motor\Media\Models\FileAssociation;

/**
 * Partymeister\Slides\Models\SlideClient
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $ip_address
 * @property string $port
 * @property int $sort_position
 * @property int|null $playlist_id
 * @property int|null $playlist_item_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property-read User $creator
 * @property-read User|null $eraser
 * @property-read User $updater
 * @method static Builder|SlideClient filteredBy(Filter $filter, $column)
 * @method static Builder|SlideClient filteredByMultiple(Filter $filter)
 * @method static Builder|SlideClient newModelQuery()
 * @method static Builder|SlideClient newQuery()
 * @method static Builder|SlideClient query()
 * @method static Builder|SlideClient search($q, $full_text = false)
 * @method static Builder|SlideClient whereCreatedAt($value)
 * @method static Builder|SlideClient whereCreatedBy($value)
 * @method static Builder|SlideClient whereDeletedBy($value)
 * @method static Builder|SlideClient whereId($value)
 * @method static Builder|SlideClient whereIpAddress($value)
 * @method static Builder|SlideClient whereName($value)
 * @method static Builder|SlideClient wherePlaylistId($value)
 * @method static Builder|SlideClient wherePlaylistItemId($value)
 * @method static Builder|SlideClient wherePort($value)
 * @method static Builder|SlideClient whereSortPosition($value)
 * @method static Builder|SlideClient whereType($value)
 * @method static Builder|SlideClient whereUpdatedAt($value)
 * @method static Builder|SlideClient whereUpdatedBy($value)
 * @mixin Eloquent
 */
class SlideClient extends Model
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
        'ip_address',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'ip_address',
        'port',
        'sort_position',
        'configuration',
        'playlist_id',
        'playlist_item_id',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    /**
     * @return MorphMany
     */
    public function file_associations()
    {
        return $this->morphMany(FileAssociation::class, 'model');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function playlist()
	{
		return $this->belongsTo(Playlist::class);
	}
}
