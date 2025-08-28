<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClassroomController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum')
        ];
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            $classrooms = $user->classrooms()
                ->withCount('students')
                ->latest()
                ->get();
        } else {
            $classrooms = $user->joinedClassrooms()
                ->with('teacher:id,name')
                ->withCount('students')
                ->latest()
                ->get();
        }

        return response()->json($classrooms);
    }

    public function store(StoreClassroomRequest $request)
    {
        Gate::authorize('create', Classroom::class);

        $data = $request->validated();

        $classroom = Classroom::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'code'        => Str::upper(Str::random(6)),
            'created_by'  => Auth::id()
        ]);

        return response()->json([
            'message'   => 'Classroom created successfully.',
            'classroom' => $classroom
        ], 201);
    }

    public function show(string $code)
    {
        $classroom = Classroom::where('code', $code)
            ->with('teacher:id,name')
            ->withCount('students')
            ->firstOrFail();

        return response()->json($classroom);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        Gate::authorize('modify', $classroom);

        $classroom->update($request->validated());

        return response()->json([
            'message' => 'Classroom updated successfully.',
            'classroom' => $classroom
        ]);
    }

    public function destroy(Classroom $classroom)
    {
        Gate::authorize('modify', $classroom);

        $classroom->delete();

        return response()->json([
            'message' => 'Classroom deleted successfully.'
        ]);
    }
}
