<?php // backend/tests/Unit/SpacedRepetitionTest.php
namespace Tests\Unit;

use App\Models\StudyVocabulary;
use App\Services\SpacedRepetition;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class SpacedRepetitionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // StudyVocabulary's `datetime` casts need a connection resolver to format
        // dates (Eloquent calls getConnection()->getQueryGrammar() even when just
        // constructing an in-memory instance with a Carbon value). This is a pure
        // PHP unit test with no Laravel app bootstrap, so we register a minimal
        // in-memory SQLite connection rather than pulling in the full Tests\TestCase.
        $capsule = new Capsule;
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    protected function setUp(): void { parent::setUp(); Carbon::setTestNow('2026-07-04 12:00:00'); }
    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    private function word(array $attrs = []): StudyVocabulary
    {
        return new StudyVocabulary(array_merge(['familiarity' => 2], $attrs));
    }

    public function test_forgot_resets_to_one_day_and_lowers_familiarity(): void
    {
        [$next, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>3]), 'forgot');
        $this->assertEquals(Carbon::now()->addDay()->toDateString(), $next->toDateString());
        $this->assertSame(2, $fam);
    }

    public function test_forgot_familiarity_floors_at_zero(): void
    {
        [, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>0]), 'forgot');
        $this->assertSame(0, $fam);
    }

    public function test_new_word_remembered_uses_base_interval(): void
    {
        // no last_reviewed_at/next_review_at -> base interval 1 -> *2 = 2 days
        [$next, $fam] = (new SpacedRepetition)->calculate($this->word(['familiarity'=>1]), 'remembered');
        $this->assertEquals(Carbon::now()->addDays(2)->toDateString(), $next->toDateString());
        $this->assertSame(2, $fam);
    }

    public function test_vague_keeps_interval_and_familiarity(): void
    {
        $w = $this->word([
            'familiarity' => 3,
            'last_reviewed_at' => Carbon::parse('2026-06-27 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-07-04 12:00:00'), // 7-day interval
        ]);
        [$next, $fam] = (new SpacedRepetition)->calculate($w, 'vague');
        $this->assertEquals(Carbon::now()->addDays(7)->toDateString(), $next->toDateString());
        $this->assertSame(3, $fam);
    }

    public function test_mastered_multiplies_interval_by_2_5_and_caps_familiarity(): void
    {
        $w = $this->word([
            'familiarity' => 5,
            'last_reviewed_at' => Carbon::parse('2026-06-30 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-07-04 12:00:00'), // 4-day interval
        ]);
        [$next, $fam] = (new SpacedRepetition)->calculate($w, 'mastered');
        $this->assertEquals(Carbon::now()->addDays(10)->toDateString(), $next->toDateString()); // 4*2.5
        $this->assertSame(5, $fam); // capped
    }

    public function test_overdue_word_does_not_produce_negative_interval(): void
    {
        // next_review_at in the past relative to now, last_reviewed even earlier
        $w = $this->word([
            'familiarity' => 2,
            'last_reviewed_at' => Carbon::parse('2026-06-20 12:00:00'),
            'next_review_at'   => Carbon::parse('2026-06-25 12:00:00'), // 5-day interval, both past
        ]);
        [$next] = (new SpacedRepetition)->calculate($w, 'remembered');
        $this->assertTrue($next->greaterThan(Carbon::now())); // never in the past
        $this->assertEquals(Carbon::now()->addDays(10)->toDateString(), $next->toDateString()); // 5*2
    }
}
