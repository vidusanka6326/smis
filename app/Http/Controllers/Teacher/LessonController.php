<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLessonRequest;
use App\Http\Requests\Teacher\UpdateLessonRequest;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Lesson::class);

        $lessons = Lesson::with(['schoolClasses', 'subject'])
            ->where('teacher_id', request()->user()->teacher->id)
            ->latest()
            ->paginate(10);

        return view('teacher.lessons.index', compact('lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Lesson::class);

        $teacher = request()->user()->teacher;

        $classes = $teacher->assignments()->with('schoolClass')->get()->pluck('schoolClass')->filter()->unique('id');
        $subjects = $teacher->assignments()->with('subject')->get()->pluck('subject')->filter()->unique('id');

        return view('teacher.lessons.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['teacher_id'] = $request->user()->teacher->id;

        $lesson = Lesson::create($data);
        $lesson->schoolClasses()->sync($request->validated('school_class_ids'));

        return redirect()->route('teacher.lessons.index')
            ->with('status', __('Lesson created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson): View
    {
        $this->authorize('view', $lesson);

        $lesson->load(['schoolClasses', 'subject', 'teacher.user']);

        return view('teacher.lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson): View
    {
        $this->authorize('update', $lesson);

        $teacher = request()->user()->teacher;
        $classes = $teacher->assignments()->with('schoolClass')->get()->pluck('schoolClass')->filter()->unique('id');
        $subjects = $teacher->assignments()->with('subject')->get()->pluck('subject')->filter()->unique('id');

        return view('teacher.lessons.edit', compact('lesson', 'classes', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $lesson->update($request->validated());
        $lesson->schoolClasses()->sync($request->validated('school_class_ids'));

        return redirect()->route('teacher.lessons.index')
            ->with('status', __('Lesson updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return redirect()->route('teacher.lessons.index')
            ->with('status', __('Lesson deleted successfully.'));
    }
}
