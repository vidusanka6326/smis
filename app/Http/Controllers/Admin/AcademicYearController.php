<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Academic\SetCurrentAcademicYear;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicYearRequest;
use App\Http\Requests\Admin\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicYear::class);

        $filters = ListQuery::filters($request, ['search', 'is_current']);

        return view('admin.academic-years.index', [
            'academicYears' => ListQuery::paginate(
                AcademicYear::query()
                    ->filter($filters)
                    ->orderByDesc('starts_on'),
                $request,
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AcademicYear::class);

        return view('admin.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request, SetCurrentAcademicYear $setCurrentAcademicYear): RedirectResponse
    {
        $data = $request->validated();
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);

        $academicYear = AcademicYear::query()->create([
            ...$data,
            'is_current' => false,
        ]);

        if ($makeCurrent) {
            $setCurrentAcademicYear->handle($academicYear);
        }

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', __('Academic year created successfully.'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        $this->authorize('update', $academicYear);

        return view('admin.academic-years.edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(
        UpdateAcademicYearRequest $request,
        AcademicYear $academicYear,
        SetCurrentAcademicYear $setCurrentAcademicYear,
    ): RedirectResponse {
        $data = $request->validated();
        $makeCurrent = (bool) ($data['is_current'] ?? false);
        unset($data['is_current']);

        $academicYear->update($data);

        if ($makeCurrent) {
            $setCurrentAcademicYear->handle($academicYear);
        } elseif ($academicYear->is_current) {
            $academicYear->forceFill(['is_current' => false])->save();
        }

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', __('Academic year updated successfully.'));
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('delete', $academicYear);

        if ($academicYear->schoolClasses()->exists()) {
            return back()->withErrors([
                'academic_year' => __('Cannot delete an academic year that has classes.'),
            ]);
        }

        $academicYear->delete();

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', __('Academic year deleted successfully.'));
    }
}
