<?php

namespace Database\Factories;

use App\Models\ReliefTeacherAssignment;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<ReliefTeacherAssignment>
 */
class ReliefTeacherAssignmentFactory extends Factory
{
    protected $model = ReliefTeacherAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entry = TimetableEntry::factory()->create();
        $date = Carbon::now()->startOfWeek(Carbon::MONDAY)
            ->addDays($entry->day_of_week->value - 1);

        if ($date->isPast()) {
            $date = $date->addWeek();
        }

        return [
            'timetable_entry_id' => $entry->id,
            'relief_teacher_id' => Teacher::factory(),
            'date' => $date->toDateString(),
            'reason' => fake()->optional()->sentence(),
            'assigned_by' => null,
        ];
    }
}
