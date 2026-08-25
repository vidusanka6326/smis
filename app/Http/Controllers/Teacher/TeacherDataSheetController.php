<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherDataSheetController extends Controller
{
    public function index(): View
    {
        return view('livewire.teacher.data-sheet.form-wrapper');
    }

    public function pdf(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $year = now()->year;
        $dataSheet = $teacher->dataSheetForYear($year)->first();

        abort_unless($dataSheet && $dataSheet->isSubmitted(), 404, 'No submitted data sheet found for this year.');

        $pdf = Pdf::loadView('teacher.data-sheet.pdf', [
            'dataSheet' => $dataSheet,
            'year' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("EMIS_Data_Sheet_{$year}_{$teacher->employee_no}.pdf");
    }
}
