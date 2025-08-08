<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassroomPolicy
{
    public function createclassroom(User $user): bool
    {
        return $user->role === 'teacher';
    }

    public function modifyclassroom(User $user, Classroom $classroom): Response
    {
        return $user->id === $classroom->created_by
            ? Response::allow()
            : Response::deny('You are not the creator of this class.');
    }
}
