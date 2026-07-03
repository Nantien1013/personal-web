<?php
namespace App\Policies;

use App\Models\{StudyVocabulary, User};

class StudyVocabularyPolicy
{
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, StudyVocabulary $vocabulary): bool { return $user->isAdmin(); }
    public function delete(User $user, StudyVocabulary $vocabulary): bool { return $user->isAdmin(); }
}
