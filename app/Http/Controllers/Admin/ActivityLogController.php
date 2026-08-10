<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $actionFilter = $request->string('action')->toString();

        $logs = ActivityLog::query()
            ->with('causer')
            ->when(
                $actionFilter !== '' && ActivityAction::tryFrom($actionFilter) !== null,
                fn ($query) => $query->where('action', $actionFilter),
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'actions' => ActivityAction::cases(),
            'selectedAction' => $actionFilter,
        ]);
    }
}
