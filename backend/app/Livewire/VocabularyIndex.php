<?php

namespace App\Livewire;

use App\Models\StudyVocabulary;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VocabularyIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $mode = 'browse'; // browse | add | flashcard

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $familiarity = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFamiliarity(): void
    {
        $this->resetPage();
    }

    public function setMode(string $mode): void
    {
        if (in_array($mode, ['browse', 'add', 'flashcard'], true)) {
            $this->mode = $mode;
        }
    }

    public function getWordsProperty()
    {
        $q = StudyVocabulary::query();

        if ($this->search !== '') {
            $q->where(function ($sub) {
                $sub->where('word', 'like', "%{$this->search}%")
                    ->orWhere('meaning', 'like', "%{$this->search}%");
            });
        }

        if ($this->familiarity !== null) {
            $q->where('familiarity', $this->familiarity);
        }

        return $q->orderByDesc('created_at')->paginate(30);
    }

    public function getStatsProperty(): array
    {
        return [
            'total'           => StudyVocabulary::count(),
            'added_this_week' => StudyVocabulary::where('created_at', '>=', now()->startOfWeek())->count(),
            'pending_review'  => StudyVocabulary::where('next_review_at', '<=', now())->count(),
            'avg_familiarity' => round(StudyVocabulary::avg('familiarity') ?? 0, 1),
        ];
    }

    #[\Livewire\Attributes\On('vocabulary-saved')]
    public function onVocabularySaved(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.vocabulary-index', [
            'words' => $this->words,
            'stats' => $this->stats,
        ])->layout('components.layouts.app', ['title' => '單字庫']);
    }
}
