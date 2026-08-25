<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lesson::class);

        $student = $request->user()->student;

        $lessons = Lesson::with(['teacher.user', 'subject'])
            ->whereHas('schoolClasses', function ($query) use ($student) {
                $query->where('classes.id', $student->current_class_id);
            })
            ->latest()
            ->paginate(12);

        return view('student.lessons.index', compact('lessons'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson): View
    {
        $this->authorize('view', $lesson);

        $lesson->load(['teacher.user', 'subject', 'schoolClasses']);

        return view('student.lessons.show', compact('lesson'));
    }
}
