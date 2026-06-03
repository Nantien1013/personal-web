<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyVocabulary extends Model
{
    protected $table = 'study_vocabulary';

    protected $fillable = [
        'word', 'meaning', 'part_of_speech', 'phonetic', 'audio_url',
        'example', 'example_zh', 'source', 'note',
        'familiarity', 'review_count', 'correct_count',
        'next_review_at', 'last_reviewed_at', 'auto_filled',
    ];

    protected $casts = [
        'familiarity' => 'integer',
        'review_count' => 'integer',
        'correct_count' => 'integer',
        'auto_filled' => 'boolean',
        'next_review_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];
}
