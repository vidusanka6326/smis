<?php

namespace App\Providers;

use App\Contracts\AgentLlm;
use App\Enums\RoleName;
use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;
use App\Services\Agent\AgentToolRegistry;
use App\Services\Agent\GeminiAgentLlm;
use App\Services\Agent\Tools\AssignReliefTeacherTool;
use App\Services\Agent\Tools\AssignTimetableSlotTool;
use App\Services\Agent\Tools\DeleteTimetableSlotTool;
use App\Services\Agent\Tools\EnterMarksTool;
use App\Services\Agent\Tools\FindFreePeriodsTool;
use App\Services\Agent\Tools\FindFreeTeachersTool;
use App\Services\Agent\Tools\GetAtRiskStudentsTool;
use App\Services\Agent\Tools\GetClassAttendanceTool;
use App\Services\Agent\Tools\GetClassTimetableTool;
use App\Services\Agent\Tools\GetDashboardSummaryTool;
use App\Services\Agent\Tools\GetExamResultsTool;
use App\Services\Agent\Tools\GetReportDataTool;
use App\Services\Agent\Tools\GetStudentSummaryTool;
use App\Services\Agent\Tools\GetTeacherTimetableTool;
use App\Services\Agent\Tools\ListActivityLogsTool;
use App\Services\Agent\Tools\ListCapabilitiesTool;
use App\Services\Agent\Tools\ListClassesTool;
use App\Services\Agent\Tools\LookupClassTool;
use App\Services\Agent\Tools\ManageAcademicYearTool;
use App\Services\Agent\Tools\ManageClassTool;
use App\Services\Agent\Tools\ManageExamTool;
use App\Services\Agent\Tools\ManageGradeTool;
use App\Services\Agent\Tools\ManageOfficerTool;
use App\Services\Agent\Tools\ManageReliefTool;
use App\Services\Agent\Tools\ManageStreamTool;
use App\Services\Agent\Tools\ManageStudentTool;
use App\Services\Agent\Tools\ManageSubjectTool;
use App\Services\Agent\Tools\ManageTeacherTool;
use App\Services\Agent\Tools\OfferChoicesTool;
use App\Services\Agent\Tools\SaveAttendanceSessionTool;
use App\Services\Agent\Tools\SaveTeacherAttendanceTool;
use App\Services\Agent\Tools\SearchExamsTool;
use App\Services\Agent\Tools\SearchStudentsTool;
use App\Services\Agent\Tools\SearchTeachersTool;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AgentLlm::class, GeminiAgentLlm::class);

        $this->app->tag([
            OfferChoicesTool::class,
            ListCapabilitiesTool::class,
            GetDashboardSummaryTool::class,
            ListClassesTool::class,
            LookupClassTool::class,
            GetClassTimetableTool::class,
            FindFreePeriodsTool::class,
            FindFreeTeachersTool::class,
            GetTeacherTimetableTool::class,
            SearchTeachersTool::class,
            AssignTimetableSlotTool::class,
            DeleteTimetableSlotTool::class,
            AssignReliefTeacherTool::class,
            ManageReliefTool::class,
            SearchStudentsTool::class,
            GetStudentSummaryTool::class,
            GetClassAttendanceTool::class,
            GetAtRiskStudentsTool::class,
            SearchExamsTool::class,
            GetExamResultsTool::class,
            ManageAcademicYearTool::class,
            ManageGradeTool::class,
            ManageStreamTool::class,
            ManageSubjectTool::class,
            ManageClassTool::class,
            ManageTeacherTool::class,
            ManageStudentTool::class,
            ManageOfficerTool::class,
            SaveAttendanceSessionTool::class,
            SaveTeacherAttendanceTool::class,
            ManageExamTool::class,
            EnterMarksTool::class,
            GetReportDataTool::class,
            ListActivityLogsTool::class,
        ], 'agent.tools');

        $this->app->bind(AgentToolRegistry::class, function ($app): AgentToolRegistry {
            return new AgentToolRegistry($app->tagged('agent.tools'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(Report::class, ReportPolicy::class);

        Route::bind('officer', function (string $value): User {
            return User::query()
                ->role(RoleName::Officer)
                ->whereKey($value)
                ->firstOrFail();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        Paginator::defaultView('pagination::flux');
    }
}
