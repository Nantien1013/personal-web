<?php // backend/app/Services/SpacedRepetition.php
namespace App\Services;

use App\Models\StudyVocabulary;
use Illuminate\Support\Carbon;

class SpacedRepetition
{
    /** @return array{0: Carbon, 1: int} */
    public function calculate(StudyVocabulary $word, string $result): array
    {
        $now      = Carbon::now();
        $interval = $this->currentIntervalDays($word); // always >= 1
        $fam      = (int) ($word->familiarity ?? 0);

        return match ($result) {
            'forgot'     => [$now->copy()->addDay(),                          max(0, $fam - 1)],
            'vague'      => [$now->copy()->addDays($interval),                $fam],
            'remembered' => [$now->copy()->addDays($interval * 2),           min(5, $fam + 1)],
            'mastered'   => [$now->copy()->addDays((int) round($interval * 2.5)), min(5, $fam + 1)],
        };
    }

    private function currentIntervalDays(StudyVocabulary $word): int
    {
        if ($word->next_review_at && $word->last_reviewed_at) {
            // Interval that was scheduled between last review and its due date.
            $days = (int) floor($word->last_reviewed_at->diffInDays($word->next_review_at, absolute: true));
            return max(1, $days);
        }
        return 1;
    }
}
