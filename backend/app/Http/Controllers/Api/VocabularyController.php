<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyVocabulary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VocabularyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StudyVocabulary::query();

        if ($request->filled('search')) {
            $query->where('word', 'like', "%{$request->search}%")
                  ->orWhere('meaning', 'like', "%{$request->search}%");
        }

        if ($request->filled('familiarity')) {
            $query->where('familiarity', $request->familiarity);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total'           => StudyVocabulary::count(),
            'added_this_week' => StudyVocabulary::where('created_at', '>=', now()->startOfWeek())->count(),
            'pending_review'  => StudyVocabulary::where('next_review_at', '<=', now())->count(),
            'avg_familiarity' => round(StudyVocabulary::avg('familiarity') ?? 0, 1),
        ]);
    }

    public function reviewQueue(): JsonResponse
    {
        $words = StudyVocabulary::where(function ($q) {
                $q->where('next_review_at', '<=', now())
                  ->orWhereNull('next_review_at');
            })
            ->orderBy('next_review_at')
            ->get();

        return response()->json($words);
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate(['word' => 'required|string|max:100']);
        $word = trim($request->word);

        $result = [
            'word'           => $word,
            'meaning'        => null,
            'part_of_speech' => null,
            'phonetic'       => null,
            'audio_url'      => null,
            'example'        => null,
        ];

        $dictResponse = Http::timeout(5)->get(
            config('services.dictionary_api') . "/api/v2/entries/en/{$word}"
        );

        if ($dictResponse->successful()) {
            $data    = $dictResponse->json()[0] ?? null;
            $meaning = $data['meanings'][0] ?? null;

            $result['part_of_speech'] = $meaning['partOfSpeech'] ?? null;
            $result['example']        = $meaning['definitions'][0]['example'] ?? null;
            $result['phonetic']       = $data['phonetic'] ?? null;

            $audioUrl = collect($data['phonetics'] ?? [])
                ->first(fn($p) => !empty($p['audio']));
            $result['audio_url'] = $audioUrl['audio'] ?? null;
        }

        $transResponse = Http::timeout(5)->get(
            config('services.mymemory_api') . '/get',
            ['q' => $word, 'langpair' => 'en|zh-TW']
        );

        if ($transResponse->successful()) {
            $result['meaning'] = $transResponse->json()['responseData']['translatedText'] ?? null;
        }

        return response()->json($result);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'word'           => 'required|string|max:100|unique:study_vocabulary,word',
            'meaning'        => 'required|string',
            'part_of_speech' => 'nullable|string|max:30',
            'phonetic'       => 'nullable|string|max:100',
            'audio_url'      => 'nullable|url|max:500',
            'example'        => 'nullable|string',
            'example_zh'     => 'nullable|string',
            'source'         => 'nullable|string|max:255',
            'note'           => 'nullable|string',
            'auto_filled'    => 'boolean',
        ]);

        $validated['next_review_at'] = now()->addDay();

        $word = StudyVocabulary::create($validated);
        return response()->json($word, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $word = StudyVocabulary::findOrFail($id);

        $validated = $request->validate([
            'word'           => "sometimes|string|max:100|unique:study_vocabulary,word,{$id}",
            'meaning'        => 'sometimes|string',
            'part_of_speech' => 'nullable|string|max:30',
            'phonetic'       => 'nullable|string|max:100',
            'audio_url'      => 'nullable|url|max:500',
            'example'        => 'nullable|string',
            'example_zh'     => 'nullable|string',
            'source'         => 'nullable|string|max:255',
            'note'           => 'nullable|string',
        ]);

        $word->update($validated);
        return response()->json($word);
    }

    public function review(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'result' => 'required|in:forgot,vague,remembered,mastered',
        ]);

        $word = StudyVocabulary::findOrFail($id);
        [$nextReview, $newFamiliarity] = $this->calculateNextReview($word, $request->result);

        $word->update([
            'next_review_at'   => $nextReview,
            'last_reviewed_at' => now(),
            'familiarity'      => $newFamiliarity,
            'review_count'     => $word->review_count + 1,
            'correct_count'    => in_array($request->result, ['remembered', 'mastered'])
                                  ? $word->correct_count + 1
                                  : $word->correct_count,
        ]);

        return response()->json($word->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        StudyVocabulary::findOrFail($id)->delete();
        return response()->json(['message' => '已刪除']);
    }

    private function calculateNextReview(StudyVocabulary $word, string $result): array
    {
        $now = now();

        $currentInterval = 1;
        if ($word->next_review_at && $word->last_reviewed_at) {
            $currentInterval = max(1, (int) $word->last_reviewed_at->diffInDays($word->next_review_at));
        }

        return match ($result) {
            'forgot'     => [$now->copy()->addDay(),                              max(0, $word->familiarity - 1)],
            'vague'      => [$now->copy()->addDays($currentInterval),             $word->familiarity],
            'remembered' => [$now->copy()->addDays($currentInterval * 2),         min(5, $word->familiarity + 1)],
            'mastered'   => [$now->copy()->addDays((int)($currentInterval * 2.5)), min(5, $word->familiarity + 1)],
        };
    }
}
