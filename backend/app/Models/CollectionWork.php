<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionWork extends Model
{
    protected $table = 'collection_works';

    protected $fillable = [
        'type', 'title', 'title_original', 'cover_url', 'status',
        'rating', 'is_favorite', 'release_year', 'release_season',
        'media_type', 'source_type', 'episodes_total', 'episodes_watched',
        'volumes_total', 'volumes_read', 'author', 'studio', 'note',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'rating' => 'integer',
        'release_year' => 'integer',
        'episodes_total' => 'integer',
        'episodes_watched' => 'integer',
        'volumes_total' => 'integer',
        'volumes_read' => 'integer',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CollectionCategory::class,
            'collection_work_categories',
            'work_id',
            'category_id'
        );
    }
}
