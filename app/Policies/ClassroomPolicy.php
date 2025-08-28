<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassroomPolicy
{
    public function create(User $user): Response
    {
        return $user->role === 'teacher'
            ? Response::allow()
            : Response::deny('You are not a teacher.');
    }

    public function modify(User $user, Classroom $classroom): Response
    {
        return $user->id === $classroom->created_by
            ? Response::allow()
            : Response::deny('You are not the creator of this class.');
    }
}
