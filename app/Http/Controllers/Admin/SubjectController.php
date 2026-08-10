<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Subject::class);

        return view('admin.subjects.index', [
            'subjects' => Subject::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Subject::class);

        return view('admin.subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::query()->create($request->validated());

        return redirect()
            ->route('admin.subjects.index')
            ->with('status', __('Subject created successfully.'));
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        return view('admin.subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()
            ->route('admin.subjects.index')
            ->with('status', __('Subject updated successfully.'));
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);

        if ($subject->schoolClasses()->exists()) {
            return back()->withErrors([
                'subject' => __('Cannot delete a subject that is assigned to classes.'),
            ]);
        }

        $subject->delete();

        return redirect()
            ->route('admin.subjects.index')
            ->with('status', __('Subject deleted successfully.'));
    }
}
