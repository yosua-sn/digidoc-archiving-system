<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Http\Requests\StoreClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ClassroomController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum')
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClassroomRequest $request)
    {
        Gate::authorize('createclassroom');
        $data = $request->validated();

        $classroom = Classroom::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'code'  => Str::random(6)
        ]);

        return response()->json($classroom, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        Gate::authorize('modifyclassroom', $classroom);
        $data = $request->validated();

        $classroom->update($data);

        return response()->json([
            'message' => 'Update classroom successfully.',
            'update' => $classroom
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        Gate::authorize('modifyclassroom', $classroom);

        $classroom->delete();

        return response()->json([
            'message' => 'Classroom delete successfully.'
        ]);
    }
}
