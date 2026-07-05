<?php

namespace App\Livewire;

use App\Models\StudyVocabulary;
use App\Services\VocabularyLookup;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VocabularyForm extends Component
{
    public string $word = '';
    public string $meaning = '';
    public string $part_of_speech = '';
    public string $phonetic = '';
    public string $audio_url = '';
    public string $example = '';
    public string $example_zh = '';
    public string $source = '';
    public string $note = '';
    public bool $auto_filled = false;

    public function lookup(VocabularyLookup $svc): void
    {
        $this->authorize('create', StudyVocabulary::class);
        $this->validate(['word' => 'required|string|max:100']);

        $r = $svc->lookup($this->word);

        $this->fill(array_filter([
            'meaning'        => $r['meaning'],
            'part_of_speech' => $r['part_of_speech'],
            'phonetic'       => $r['phonetic'],
            'audio_url'      => $r['audio_url'],
            'example'        => $r['example'],
        ], fn ($v) => $v !== null));

        $this->auto_filled = true;
    }

    public function save(): void
    {
        $this->authorize('create', StudyVocabulary::class);

        $validated = $this->validate([
            'word'           => 'required|string|max:100|unique:study_vocabulary,word',
            'meaning'        => 'required|string',
            'part_of_speech' => 'nullable|string|max:30',
            'phonetic'       => 'nullable|string|max:100',
            'audio_url'      => 'nullable|url|max:500',
            'example'        => 'nullable|string',
            'example_zh'     => 'nullable|string',
            'source'         => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        $validated['auto_filled'] = $this->auto_filled;
        $validated['next_review_at'] = now()->addDay();

        StudyVocabulary::create($validated);

        $this->reset([
            'word', 'meaning', 'part_of_speech', 'phonetic', 'audio_url',
            'example', 'example_zh', 'source', 'note', 'auto_filled',
        ]);

        $this->dispatch('vocabulary-saved');
        $this->dispatch('toast', message: '單字已新增');
    }

    public function render(): View
    {
        return view('livewire.vocabulary-form');
    }
}
