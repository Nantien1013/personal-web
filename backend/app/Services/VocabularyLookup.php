<?php // backend/app/Services/VocabularyLookup.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VocabularyLookup
{
    /** @return array{word:string,meaning:?string,part_of_speech:?string,phonetic:?string,audio_url:?string,example:?string} */
    public function lookup(string $word): array
    {
        $word = trim($word);
        return Cache::remember("vocab_lookup:".mb_strtolower($word), now()->addDay(), function () use ($word) {
            return array_merge(
                ['word' => $word, 'meaning' => null, 'part_of_speech' => null,
                 'phonetic' => null, 'audio_url' => null, 'example' => null],
                $this->fromDictionary($word),
                $this->fromTranslator($word),
            );
        });
    }

    private function fromDictionary(string $word): array
    {
        try {
            $res = Http::timeout(5)->get(config('services.dictionary_api')."/api/v2/entries/en/{$word}");
            if (!$res->successful()) return [];
            $data = $res->json()[0] ?? null;
            if (!$data) return [];
            $meaning = $data['meanings'][0] ?? [];
            $audio = collect($data['phonetics'] ?? [])->first(fn ($p) => !empty($p['audio']));
            return [
                'part_of_speech' => $meaning['partOfSpeech'] ?? null,
                'example'        => $meaning['definitions'][0]['example'] ?? null,
                'phonetic'       => $data['phonetic'] ?? null,
                'audio_url'      => $audio['audio'] ?? null,
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function fromTranslator(string $word): array
    {
        try {
            $res = Http::timeout(5)->get(config('services.mymemory_api').'/get', [
                'q' => $word, 'langpair' => 'en|zh-TW',
            ]);
            if (!$res->successful()) return [];
            return ['meaning' => $res->json()['responseData']['translatedText'] ?? null];
        } catch (\Throwable) {
            return [];
        }
    }
}
