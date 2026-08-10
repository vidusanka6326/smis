<?php

namespace Database\Factories;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'causer_id' => User::factory(),
            'action' => ActivityAction::UserCreated,
            'subject_type' => (new User)->getMorphClass(),
            'subject_id' => User::factory(),
            'description' => 'User account created.',
            'properties' => ['role' => 'teacher'],
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ];
    }
}
