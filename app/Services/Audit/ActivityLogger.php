<?php

namespace App\Services\Audit;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Persist an audit trail entry for a sensitive domain action.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(
        ActivityAction $action,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        ?User $causer = null,
    ): ActivityLog {
        $causer ??= Auth::user();

        return ActivityLog::query()->create([
            'causer_id' => $causer?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
