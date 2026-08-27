# Design Patterns Used in SMIS

This document presents strong, application-visible design patterns used in the SMIS Laravel system. The first five are the selected top patterns, followed by the API Integration Pattern requested as an additional pattern. Screenshot targets identify the files and lines that best demonstrate each pattern. Line numbers refer to the current source and can change after future edits.

## Pattern Summary

| Pattern | Main evidence | Screenshot target |
| --- | --- | --- |
| Command | `app/Actions/Examination/UpsertMarks.php` | Lines 14-111 |
| Service Layer | `app/Services/Attendance/AttendancePercentageCalculator.php` | Lines 8-75 |
| Strategy | `app/Contracts/AgentLlm.php`, `app/Services/Agent/GeminiAgentLlm.php` | Contract lines 7-18; implementation lines 12-49 |
| Registry | `app/Services/Agent/AgentToolRegistry.php` | Lines 9-83 |
| Middleware | `app/Http/Middleware/EnsureUserIsActive.php`, `bootstrap/app.php` | Middleware lines 9-35; registration lines 16-26 |
| API Integration | `app/Services/Agent/GeminiAgentLlm.php` | Lines 21-75 and 105-170 |

## 1. Command Pattern: Application Actions

**Screenshot:** `app/Actions/Examination/UpsertMarks.php`, lines 14-111. Capture the constructor and the complete `handle` method. A supporting screenshot may include `app/Http/Controllers/Admin/MarkEntryController.php`, lines 18-22, where the action is injected and called.

The application Actions represent the Command pattern by packaging a business operation as a dedicated object. `UpsertMarks` exposes one `handle` method that validates eligibility, calculates results, updates or removes records, runs the changes transactionally, and records activity. A controller can invoke this command without owning the workflow details. The same action can also be reused by another interface, keeping examination behavior consistent.

## 2. Service Layer

**Screenshot:** `app/Services/Attendance/AttendancePercentageCalculator.php`, lines 8-75. Capture the class and both public calculation methods.

The Service Layer pattern is used to keep reusable domain logic outside controllers and database models. `AttendancePercentageCalculator` owns the attendance rules for present, late, absent, and excused statuses, then exposes focused methods for calculating percentages. Reporting and dashboard features can call this service with the same rules. This produces a cohesive unit with explicit inputs and outputs, while avoiding duplicated calculations across different user workflows.

## 3. Strategy Pattern

**Screenshot:** `app/Contracts/AgentLlm.php`, lines 7-18, together with `app/Services/Agent/GeminiAgentLlm.php`, lines 12-49. Capture the interface and its concrete implementation side by side if possible.

The Strategy pattern appears in the language-model integration. `AgentLlm` defines the behavior required to stream a model turn and check configuration, while `GeminiAgentLlm` supplies one interchangeable strategy. `AgentOrchestrator` depends on the contract rather than Gemini-specific details. A different provider or a test double can therefore implement the same interface without requiring changes to the orchestration workflow, reducing coupling and improving testability.

## 4. Registry Pattern

**Screenshot:** `app/Services/Agent/AgentToolRegistry.php`, lines 9-83. Capture the constructor, `forUser`, `declarationsFor`, and `execute` methods. The tagged tool list in `app/Providers/AppServiceProvider.php`, lines 64-101, is supporting evidence.

The agent tools use the Registry pattern to collect and locate capabilities by name. `AgentToolRegistry` stores an iterable of `AgentTool` objects, filters them according to the signed-in user, exposes declarations for the model, and dispatches execution to the selected tool. The registry centralizes discovery, authorization filtering, and error normalization, so the orchestrator does not need hard-coded knowledge of every available school-management operation.

## 5. Middleware Pattern

**Screenshot:** `app/Http/Middleware/EnsureUserIsActive.php`, lines 9-35, together with `bootstrap/app.php`, lines 16-26. A supporting screenshot may include `routes/web.php`, lines 68-69, showing the `auth`, `verified`, and `active` middleware group.

The Middleware pattern places request-processing concerns in reusable components that surround the main application action. `EnsureUserIsActive` checks the authenticated user's status before allowing the request to continue, logging out inactive accounts and redirecting them when necessary. Laravel registers it as the `active` alias, and the web routes apply that alias to protected pages. This keeps access protection centralized and consistently enforced.

## 6. API Integration Pattern

**Screenshot:** `app/Services/Agent/GeminiAgentLlm.php`, lines 21-75 and 105-170. Capture the request payload, HTTP headers, endpoint construction, retry logic, response validation, and event conversion.

The API Integration pattern isolates communication with the external Gemini service inside `GeminiAgentLlm`. The class constructs provider-specific payloads, adds authentication headers, resolves the endpoint, retries temporary failures, handles connection errors, validates responses, and converts results into SMIS events. Other application classes use the internal `AgentLlm` contract instead of making HTTP requests directly. This centralizes external-service complexity and makes future provider changes easier.

## Why These Five Were Selected

These patterns are the clearest examples because each has dedicated application code and a visible responsibility: Actions execute commands, services own domain calculations, strategies isolate interchangeable providers, registries manage dynamic tools, and middleware protects or prepares requests. Other Laravel features, such as model factories, are present but primarily support testing or framework conventions, so they are not included among the system's top application patterns.

## Testing Evidence

The following test cases can be included in the report to demonstrate that the documented patterns work correctly. These tests already exist in the project, so the test name and result can be used as evidence.

| Pattern | Test file | Test case to show |
| --- | --- | --- |
| Command | `tests/Feature/Admin/MarkEntryTest.php` | `admin mark entry computes fail below pass mark` |
| Command | `tests/Feature/Admin/MarkEntryTest.php` | `marks cannot be edited after publish` |
| Service Layer | `tests/Unit/Attendance/AttendancePercentageCalculatorTest.php` | `present and late count as attended` |
| Service Layer | `tests/Unit/Attendance/AttendancePercentageCalculatorTest.php` | `excused days are excluded from denominator` |
| Strategy | `tests/Unit/Agent/GeminiAgentLlmTest.php` | `generateContent yields text from gemini-flash-latest` |
| Strategy | `tests/Feature/Agent/AgentOrchestratorTest.php` | `orchestrator calls tools then stores markdown and choices` |
| Registry | `tests/Feature/Agent/AgentCoverageTest.php` | `teacher cannot create a grade through the registry` |
| Registry | `tests/Feature/Agent/AgentCoverageTest.php` | `capabilities lists the signed-in user's permissions` |
| Middleware | `tests/Feature/Auth/InactiveUserLoginTest.php` | `active middleware logs out inactive users` |
| API Integration | `tests/Unit/Agent/GeminiAgentLlmTest.php` | `generateContent yields text from gemini-flash-latest` |
| API Integration | `tests/Unit/Agent/GeminiAgentLlmTest.php` | `generateContent yields function calls and converts json schema types` |

### Recommended Additional Cases

If extra test evidence is required, add these focused cases:

- The Command action rejects duplicate student mark rows and rolls back the transaction when a mark is invalid.
- The Service Layer rejects negative attendance counts and returns zero for an empty or fully excused attendance list.
- The Strategy implementation converts provider errors and connection timeouts into `AgentLlmException`.
- The Registry returns an error for an unknown tool and hides tools unauthorized for the current role.
- The Middleware allows active users through and applies the selected locale from the session or user account.