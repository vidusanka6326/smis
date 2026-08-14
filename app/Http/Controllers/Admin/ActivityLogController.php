<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityAction;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $filters = ListQuery::filters($request, ['action', 'search', 'date_from', 'date_to']);

        return view('admin.activity-logs.index', [
            'logs' => ListQuery::paginate(
                ActivityLog::query()
                    ->with('causer')
                    ->filter($filters)
                    ->latest('created_at')
                    ->latest('id'),
                $request,
            ),
            'filters' => $filters,
            'actions' => ActivityAction::cases(),
        ]);
    }
}
