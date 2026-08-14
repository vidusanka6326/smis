<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGradeRequest;
use App\Http\Requests\Admin\UpdateGradeRequest;
use App\Models\Grade;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $filters = ListQuery::filters($request, ['search']);

        return view('admin.grades.index', [
            'grades' => ListQuery::paginate(
                Grade::query()->filter($filters)->orderBy('number'),
                $request,
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Grade::class);

        return view('admin.grades.create');
    }

    public function store(StoreGradeRequest $request): RedirectResponse
    {
        Grade::query()->create($request->validated());

        return redirect()
            ->route('admin.grades.index')
            ->with('status', __('Grade created successfully.'));
    }

    public function edit(Grade $grade): View
    {
        $this->authorize('update', $grade);

        return view('admin.grades.edit', [
            'grade' => $grade,
        ]);
    }

    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $grade->update($request->validated());

        return redirect()
            ->route('admin.grades.index')
            ->with('status', __('Grade updated successfully.'));
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $this->authorize('delete', $grade);

        if ($grade->schoolClasses()->exists()) {
            return back()->withErrors([
                'grade' => __('Cannot delete a grade that has classes.'),
            ]);
        }

        $grade->delete();

        return redirect()
            ->route('admin.grades.index')
            ->with('status', __('Grade deleted successfully.'));
    }
}
