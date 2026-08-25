<?php

use App\Livewire\Teacher\DataSheet\Form;
use App\Models\Teacher;
use App\Models\TeacherDataSheet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the data sheet form for a teacher', function () {
    $teacher = Teacher::factory()->create();
    
    actingAs($teacher->user)
        ->get(route('teacher.data-sheet.index'))
        ->assertOk()
        ->assertSeeLivewire(Form::class);
});

it('can save a draft without submitting', function () {
    $teacher = Teacher::factory()->create();
    actingAs($teacher->user);

    Livewire::test(Form::class)
        ->set('nic', '123456789V')
        ->set('full_name', 'Test Teacher')
        ->call('saveDraft');

    $this->assertDatabaseHas('teacher_data_sheets', [
        'teacher_id' => $teacher->id,
        'year' => now()->year,
        'nic' => '123456789V',
        'submitted_at' => null,
    ]);
});

it('can submit the data sheet and prevent further edits', function () {
    $teacher = Teacher::factory()->create();
    actingAs($teacher->user);

    Livewire::test(Form::class)
        ->set('school_census_no', 'CE-1234')
        ->set('nic', '123456789V')
        ->set('full_name', 'Test Teacher')
        ->call('submitForm')
        ->assertSet('isSubmitted', true);

    $dataSheet = TeacherDataSheet::where('teacher_id', $teacher->id)->first();
    expect($dataSheet->submitted_at)->not->toBeNull();
});

it('prevents generating pdf for unsubmitted data sheet', function () {
    $teacher = Teacher::factory()->create();
    TeacherDataSheet::factory()->create([
        'teacher_id' => $teacher->id,
        'submitted_at' => null,
    ]);

    actingAs($teacher->user)
        ->get(route('teacher.data-sheet.pdf'))
        ->assertNotFound();
});

it('can generate pdf for submitted data sheet', function () {
    $teacher = Teacher::factory()->create();
    TeacherDataSheet::factory()->submitted()->create([
        'teacher_id' => $teacher->id,
    ]);

    actingAs($teacher->user)
        ->get(route('teacher.data-sheet.pdf'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
