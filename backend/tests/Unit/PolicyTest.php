<?php // backend/tests/Unit/PolicyTest.php
namespace Tests\Unit;

use App\Models\{CollectionWork, StudyVocabulary, User};
use App\Policies\{CollectionWorkPolicy, StudyVocabularyPolicy};
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase
{
    public function test_admin_can_write_collection(): void
    {
        $admin = new User(['role' => 'admin']);
        $this->assertTrue((new CollectionWorkPolicy)->create($admin));
        $this->assertTrue((new CollectionWorkPolicy)->update($admin, new CollectionWork));
    }
    public function test_user_cannot_write_collection(): void
    {
        $user = new User(['role' => 'user']);
        $this->assertFalse((new CollectionWorkPolicy)->create($user));
    }
    public function test_admin_only_vocabulary(): void
    {
        $this->assertTrue((new StudyVocabularyPolicy)->delete(new User(['role'=>'admin']), new StudyVocabulary));
        $this->assertFalse((new StudyVocabularyPolicy)->delete(new User(['role'=>'user']), new StudyVocabulary));
    }
}
