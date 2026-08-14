<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Officers\CreateOfficer;
use App\Actions\Officers\UpdateOfficer;
use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOfficerRequest;
use App\Http\Requests\Admin\UpdateOfficerRequest;
use App\Models\User;
use App\Support\ListQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageOfficers', User::class);

        $filters = ListQuery::filters($request, ['search', 'status']);

        return view('admin.officers.index', [
            'officers' => ListQuery::paginate(
                User::query()
                    ->role(RoleName::Officer)
                    ->filter($filters)
                    ->latest('id'),
                $request,
            ),
            'filters' => $filters,
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('manageOfficers', User::class);

        return view('admin.officers.create', [
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function store(StoreOfficerRequest $request, CreateOfficer $createOfficer): RedirectResponse
    {
        $officer = $createOfficer->handle($request->validated());

        return redirect()
            ->route('admin.officers.index')
            ->with('status', __('Officer :name created successfully.', ['name' => $officer->name]));
    }

    public function edit(User $officer): View
    {
        $this->authorize('updateOfficer', $officer);

        return view('admin.officers.edit', [
            'officer' => $officer,
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function update(UpdateOfficerRequest $request, User $officer, UpdateOfficer $updateOfficer): RedirectResponse
    {
        $updateOfficer->handle($officer, $request->validated());

        return redirect()
            ->route('admin.officers.index')
            ->with('status', __('Officer updated successfully.'));
    }

    public function destroy(User $officer): RedirectResponse
    {
        $this->authorize('deleteOfficer', $officer);

        $officer->delete();

        return redirect()
            ->route('admin.officers.index')
            ->with('status', __('Officer removed.'));
    }
}
