<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollectionCategory extends Model
{
    use HasFactory;
    protected $table = 'collection_categories';
    public $timestamps = false;

    protected $fillable = ['name', 'group', 'display_order'];

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(
            CollectionWork::class,
            'collection_work_categories',
            'category_id',
            'work_id'
        );
    }
}
