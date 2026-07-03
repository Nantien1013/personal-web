<?php // backend/tests/Unit/VocabularyLookupTest.php
namespace Tests\Unit;

use App\Services\VocabularyLookup;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VocabularyLookupTest extends TestCase
{
    public function test_merges_both_apis(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response([[
                'phonetic' => '/tɛst/',
                'phonetics' => [['audio' => 'https://a/test.mp3']],
                'meanings' => [['partOfSpeech' => 'noun',
                    'definitions' => [['example' => 'a test sentence']]]],
            ]], 200),
            'api.mymemory.translated.net/*' => Http::response([
                'responseData' => ['translatedText' => '測試'],
            ], 200),
        ]);

        $r = (new VocabularyLookup)->lookup('test');

        $this->assertSame('test', $r['word']);
        $this->assertSame('測試', $r['meaning']);
        $this->assertSame('noun', $r['part_of_speech']);
        $this->assertSame('/tɛst/', $r['phonetic']);
        $this->assertSame('https://a/test.mp3', $r['audio_url']);
        $this->assertSame('a test sentence', $r['example']);
    }

    public function test_dictionary_failure_still_returns_translation(): void
    {
        Http::fake([
            'api.dictionaryapi.dev/*' => Http::response('not found', 404),
            'api.mymemory.translated.net/*' => Http::response(['responseData' => ['translatedText' => '測試']], 200),
        ]);
        $r = (new VocabularyLookup)->lookup('test');
        $this->assertSame('測試', $r['meaning']);
        $this->assertNull($r['part_of_speech']);
    }

    public function test_total_failure_returns_nulls_without_throwing(): void
    {
        Http::fake(fn () => Http::response('', 500));
        $r = (new VocabularyLookup)->lookup('test');
        $this->assertSame('test', $r['word']);
        $this->assertNull($r['meaning']);
    }
}
