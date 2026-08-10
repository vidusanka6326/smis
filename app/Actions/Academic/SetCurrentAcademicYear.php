<?php

namespace App\Actions\Academic;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class SetCurrentAcademicYear
{
    /**
     * Mark the given academic year as current and clear the flag on all others.
     */
    public function handle(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear): AcademicYear {
            AcademicYear::query()
                ->whereKeyNot($academicYear->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $academicYear->forceFill(['is_current' => true])->save();

            return $academicYear->refresh();
        });
    }
}
