<?php

namespace App\Services\Agent;

use App\Models\User;
use App\Services\Agent\Tools\AssignReliefTeacherTool;
use App\Services\Agent\Tools\AssignTimetableSlotTool;
use App\Services\Agent\Tools\FindFreePeriodsTool;
use App\Services\Agent\Tools\FindFreeTeachersTool;
use App\Services\Agent\Tools\GetAtRiskStudentsTool;
use App\Services\Agent\Tools\GetClassAttendanceTool;
use App\Services\Agent\Tools\GetClassTimetableTool;
use App\Services\Agent\Tools\GetExamResultsTool;
use App\Services\Agent\Tools\GetStudentSummaryTool;
use App\Services\Agent\Tools\GetTeacherTimetableTool;
use App\Services\Agent\Tools\ListClassesTool;
use App\Services\Agent\Tools\LookupClassTool;
use App\Services\Agent\Tools\OfferChoicesTool;
use App\Services\Agent\Tools\SearchExamsTool;
use App\Services\Agent\Tools\SearchStudentsTool;
use App\Services\Agent\Tools\SearchTeachersTool;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class AgentToolRegistry
{
    /**
     * @var list<AgentTool>
     */
    private array $tools;

    public function __construct(
        OfferChoicesTool $offerChoices,
        ListClassesTool $listClasses,
        LookupClassTool $lookupClass,
        GetClassTimetableTool $getClassTimetable,
        FindFreePeriodsTool $findFreePeriods,
        FindFreeTeachersTool $findFreeTeachers,
        GetTeacherTimetableTool $getTeacherTimetable,
        SearchTeachersTool $searchTeachers,
        AssignTimetableSlotTool $assignTimetableSlot,
        AssignReliefTeacherTool $assignReliefTeacher,
        SearchStudentsTool $searchStudents,
        GetStudentSummaryTool $getStudentSummary,
        GetClassAttendanceTool $getClassAttendance,
        GetAtRiskStudentsTool $getAtRiskStudents,
        SearchExamsTool $searchExams,
        GetExamResultsTool $getExamResults,
    ) {
        $this->tools = [
            $offerChoices,
            $listClasses,
            $lookupClass,
            $getClassTimetable,
            $findFreePeriods,
            $findFreeTeachers,
            $getTeacherTimetable,
            $searchTeachers,
            $assignTimetableSlot,
            $assignReliefTeacher,
            $searchStudents,
            $getStudentSummary,
            $getClassAttendance,
            $getAtRiskStudents,
            $searchExams,
            $getExamResults,
        ];
    }

    /**
     * @return list<AgentTool>
     */
    public function forUser(User $user): array
    {
        return array_values(array_filter(
            $this->tools,
            fn (AgentTool $tool): bool => $tool->authorized($user),
        ));
    }

    /**
     * Gemini functionDeclarations for the signed-in user.
     *
     * @return list<array<string, mixed>>
     */
    public function declarationsFor(User $user): array
    {
        return array_map(fn (AgentTool $tool): array => [
            'name' => $tool->name(),
            'description' => $tool->description(),
            'parameters' => $tool->parameters(),
        ], $this->forUser($user));
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function execute(User $user, string $name, array $arguments): array
    {
        $tool = collect($this->forUser($user))->first(
            fn (AgentTool $candidate): bool => $candidate->name() === $name,
        );

        if ($tool === null) {
            return [
                'ok' => false,
                'error' => __('That action is not available for your role.'),
            ];
        }

        try {
            return $tool->handle($user, $arguments);
        } catch (AuthorizationException) {
            return [
                'ok' => false,
                'error' => __('You do not have permission to do that.'),
            ];
        } catch (ValidationException $exception) {
            return [
                'ok' => false,
                'error' => collect($exception->errors())->flatten()->first() ?: $exception->getMessage(),
            ];
        }
    }
}
