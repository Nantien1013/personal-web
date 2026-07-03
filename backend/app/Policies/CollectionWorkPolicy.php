<?php
namespace App\Policies;

use App\Models\{CollectionWork, User};

class CollectionWorkPolicy
{
    public function create(User $user): bool { return $user->isAdmin(); }
    public function update(User $user, CollectionWork $work): bool { return $user->isAdmin(); }
    public function delete(User $user, CollectionWork $work): bool { return $user->isAdmin(); }
}
