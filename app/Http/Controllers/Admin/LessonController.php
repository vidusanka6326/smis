<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
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

        $lessons = Lesson::with(['teacher.user', 'schoolClasses', 'subject'])
            ->latest()
            ->paginate(12);

        return view('admin.lessons.index', compact('lessons'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson): View
    {
        $this->authorize('view', $lesson);

        $lesson->load(['teacher.user', 'schoolClasses', 'subject']);

        return view('admin.lessons.show', compact('lesson'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return redirect()->route('admin.lessons.index')
            ->with('status', __('Lesson deleted successfully.'));
    }
}
