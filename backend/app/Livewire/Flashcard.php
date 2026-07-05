<?php

namespace App\Livewire;

use App\Models\StudyVocabulary;
use App\Services\SpacedRepetition;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Flashcard extends Component
{
    public array $queue = [];
    public int $index = 0;
    public bool $flipped = false;
    public int $reviewedToday = 0;

    public function mount(): void
    {
        $this->loadQueue();
    }

    public function loadQueue(): void
    {
        $this->queue = StudyVocabulary::query()
            ->where(fn ($q) => $q->where('next_review_at', '<=', now())->orWhereNull('next_review_at'))
            ->orderBy('next_review_at')
            ->get()
            ->toArray();

        $this->index = 0;
        $this->flipped = false;
    }

    public function flip(): void
    {
        $this->flipped = ! $this->flipped;
    }

    public function rate(string $result, SpacedRepetition $svc): void
    {
        abort_if(! in_array($result, ['forgot', 'vague', 'remembered', 'mastered'], true), 422);

        $card = StudyVocabulary::findOrFail($this->queue[$this->index]['id']);
        $this->authorize('update', $card);
        [$next, $fam] = $svc->calculate($card, $result);

        $card->update([
            'next_review_at'   => $next,
            'last_reviewed_at' => now(),
            'familiarity'      => $fam,
            'review_count'     => $card->review_count + 1,
            'correct_count'    => in_array($result, ['remembered', 'mastered'], true)
                ? $card->correct_count + 1
                : $card->correct_count,
        ]);

        $this->reviewedToday++;
        $this->index++;
        $this->flipped = false;
    }

    public function render(): View
    {
        return view('livewire.flashcard');
    }
}
