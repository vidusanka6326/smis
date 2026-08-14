<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('Smart School Data Gathering & Management System — attendance, timetables, examinations, student records, and reports for Admin, Teacher, and Student roles.') }}">

        <title>{{ config('app.name', 'SMIS') }} — {{ __('Smart School Management') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
        <link rel="icon" href="/favicon-192x192.png" type="image/png" sizes="192x192">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=sora:500,600,700|source-serif-4:400,500" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --home-ink: #14242b;
                --home-teal: #0f6b6d;
                --home-mist: #edf3f4;
                --home-sand: #d7e3e4;
            }

            .font-display {
                font-family: 'Sora', ui-sans-serif, system-ui, sans-serif;
            }

            .font-reading {
                font-family: 'Source Serif 4', ui-serif, Georgia, serif;
            }

            .home-hero-media {
                animation: home-ken 28s ease-in-out infinite alternate;
            }

            .home-rise {
                animation: home-rise 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
            }

            .home-rise-delay-1 { animation-delay: 0.12s; }
            .home-rise-delay-2 { animation-delay: 0.24s; }
            .home-rise-delay-3 { animation-delay: 0.36s; }

            @keyframes home-ken {
                from { transform: scale(1.05) translate3d(0, 0, 0); }
                to { transform: scale(1.12) translate3d(-1.5%, -1%, 0); }
            }

            @keyframes home-rise {
                from {
                    opacity: 0;
                    transform: translate3d(0, 1.25rem, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .home-hero-media,
                .home-rise {
                    animation: none !important;
                }
            }
        </style>
    </head>
    <body class="bg-[var(--home-mist)] text-[var(--home-ink)] antialiased">
        <header class="absolute inset-x-0 top-0 z-20">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                    <img
                        src="{{ asset('images/smis-logo-home.png') }}"
                        alt="{{ config('app.name', 'SMIS') }}"
                        class="h-10 w-10 object-contain drop-shadow-md"
                        width="40"
                        height="40"
                    >
                    <span class="font-display text-lg font-semibold tracking-tight">
                        {{ config('app.name', 'SMIS') }}
                    </span>
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        {{-- <a href="#modules" class="hidden font-display text-sm font-medium text-white/85 transition hover:text-white sm:inline">
                            {{ __('Modules') }}
                        </a>
                        <a href="#roles" class="hidden font-display text-sm font-medium text-white/85 transition hover:text-white sm:inline">
                            {{ __('Roles') }}
                        </a> --}}
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-md bg-white/95 px-4 py-2 font-display text-sm font-semibold text-[var(--home-ink)] transition hover:bg-white"
                            >
                                {{ __('Open dashboard') }}
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md bg-white/95 px-4 py-2 font-display text-sm font-semibold text-[var(--home-ink)] transition hover:bg-white"
                            >
                                {{ __('Log in') }}
                            </a>
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <section class="relative min-h-[100svh] overflow-hidden">
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=2400&q=80"
                    alt="{{ __('Classroom ready for the school day') }}"
                    class="home-hero-media h-full w-full object-cover"
                    width="2400"
                    height="1600"
                    fetchpriority="high"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-[var(--home-ink)]/90 via-[var(--home-ink)]/70 to-[var(--home-teal)]/35"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[var(--home-ink)]/80 via-transparent to-[var(--home-ink)]/30"></div>
            </div>

            <div class="relative z-10 mx-auto flex min-h-[100svh] max-w-6xl flex-col justify-end px-6 pb-16 pt-28 lg:justify-center lg:px-8 lg:pb-24 lg:pt-20">
                <h1 class="home-rise home-rise-delay-1 mt-5 max-w-2xl font-display text-2xl font-semibold leading-tight text-white sm:text-3xl lg:text-4xl">
                    {{ __('School data, gathered and managed in one place.') }}
                </h1>

                <p class="home-rise home-rise-delay-2 mt-4 max-w-xl font-reading text-lg leading-relaxed text-white/85">
                    {{ __('Attendance, timetables, examinations, and reports for administrators, teachers, and students.') }}
                </p>

                <div class="home-rise home-rise-delay-3 mt-8 flex flex-wrap items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-md bg-[var(--home-teal)] px-5 py-3 font-display text-sm font-semibold text-white transition hover:bg-[#0c585a]"
                        >
                            {{ __('Continue to dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-md bg-[var(--home-teal)] px-5 py-3 font-display text-sm font-semibold text-white transition hover:bg-[#0c585a]"
                        >
                            {{ __('Sign in to SMIS') }}
                        </a>
                    @endauth
                    <a href="#modules" class="rounded-md border border-white/40 px-5 py-3 font-display text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
                        {{ __('See what’s included') }}
                    </a>
                </div>
            </div>
        </section>

        <section id="modules" class="scroll-mt-8 border-t border-[var(--home-sand)] bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-[var(--home-ink)]">
                    {{ __('What SMIS runs every day') }}
                </h2>
                <p class="mt-3 max-w-2xl font-reading text-lg text-[var(--home-ink)]/75">
                    {{ __('From academic structure to published results, SMIS keeps school operations connected and auditable.') }}
                </p>

                <div class="mt-14 grid gap-x-12 gap-y-12 md:grid-cols-2">
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('People & structure') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Manage academic years, grades 1–13, streams for A/L, subjects, and classes. Maintain teacher profiles with class, subject, and PT/PD assignments, plus student enrollment and guardian details.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Attendance') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Take class or subject sessions, mark present / absent / late / excused, finalize records, track teacher attendance, and review monthly percentages with clear formulas.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Timetables & relief') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Build Mon–Fri period grids with clock times, prevent teacher double-booking, and manually assign relief teachers when a slot needs cover.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Exams & marks') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Configure term tests, scholarship, O/L, and A/L exams. Enter marks with automatic grade letters and pass/fail, then publish so students can view locked results.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Reporting & analytics') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Dashboards with Chart.js charts, demographics, attendance trends, examination stats, best/poor performers, plus CSV download and print-ready views.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Security & audit') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Spatie roles and Laravel policies enforce access. Sensitive actions — user creation, marks, publish, attendance edits — are written to an admin activity log.') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section id="roles" class="scroll-mt-8 border-t border-[var(--home-sand)] bg-[var(--home-mist)]">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-[var(--home-ink)]">
                    {{ __('Built around three school roles') }}
                </h2>
                <p class="mt-3 max-w-2xl font-reading text-lg text-[var(--home-ink)]/75">
                    {{ __('Everyone signs in to the same system. What they can see and change depends on role and class assignments.') }}
                </p>

                <div class="mt-14 space-y-10">
                    <article class="border-t border-[var(--home-sand)] pt-8 md:grid md:grid-cols-[10rem_1fr] md:gap-10">
                        <h3 class="font-display text-lg font-semibold text-[var(--home-teal)]">{{ __('Admin') }}</h3>
                        <div class="mt-3 space-y-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80 md:mt-0">
                            <p>{{ __('Owns the school configuration: create user accounts, manage teachers and students, set up academic structure, build timetables, oversee attendance and exams, publish results, and review activity logs and school-wide reports.') }}</p>
                        </div>
                    </article>
                    <article class="border-t border-[var(--home-sand)] pt-8 md:grid md:grid-cols-[10rem_1fr] md:gap-10">
                        <h3 class="font-display text-lg font-semibold text-[var(--home-teal)]">{{ __('Teacher') }}</h3>
                        <div class="mt-3 space-y-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80 md:mt-0">
                            <p>{{ __('Works inside assigned classes and subjects. Class teachers manage their roster and can take class-wide attendance. Subject teachers enter period attendance and marks for their subjects. Everyone sees their own teaching timetable and scoped analytics.') }}</p>
                        </div>
                    </article>
                    <article class="border-t border-[var(--home-sand)] pt-8 md:grid md:grid-cols-[10rem_1fr] md:gap-10">
                        <h3 class="font-display text-lg font-semibold text-[var(--home-teal)]">{{ __('Student') }}</h3>
                        <div class="mt-3 space-y-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80 md:mt-0">
                            <p>{{ __('Read-only access to their own world: class timetable, attendance history and monthly percentage, published exam results, and a personal performance report.') }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="border-t border-[var(--home-sand)] bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-[var(--home-ink)]">
                    {{ __('How a school week moves through SMIS') }}
                </h2>
                <p class="mt-3 max-w-2xl font-reading text-lg text-[var(--home-ink)]/75">
                    {{ __('A simple operating rhythm — configure once, then capture and review every day.') }}
                </p>

                <ol class="mt-14 space-y-8">
                    <li class="grid gap-3 border-t border-[var(--home-sand)] pt-8 md:grid-cols-[4rem_1fr] md:gap-8">
                        <span class="font-display text-2xl font-semibold text-[var(--home-teal)]">01</span>
                        <div>
                            <h3 class="font-display text-lg font-semibold">{{ __('Configure the year') }}</h3>
                            <p class="mt-2 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                                {{ __('Admins set the current academic year, grades, streams, subjects, and classes, then assign teachers and enroll students.') }}
                            </p>
                        </div>
                    </li>
                    <li class="grid gap-3 border-t border-[var(--home-sand)] pt-8 md:grid-cols-[4rem_1fr] md:gap-8">
                        <span class="font-display text-2xl font-semibold text-[var(--home-teal)]">02</span>
                        <div>
                            <h3 class="font-display text-lg font-semibold">{{ __('Schedule the week') }}</h3>
                            <p class="mt-2 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                                {{ __('Build class timetables with conflict checks. Teachers and students open the same grid from their dashboards.') }}
                            </p>
                        </div>
                    </li>
                    <li class="grid gap-3 border-t border-[var(--home-sand)] pt-8 md:grid-cols-[4rem_1fr] md:gap-8">
                        <span class="font-display text-2xl font-semibold text-[var(--home-teal)]">03</span>
                        <div>
                            <h3 class="font-display text-lg font-semibold">{{ __('Capture the day') }}</h3>
                            <p class="mt-2 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                                {{ __('Mark attendance, finalize sessions, enter exam marks during assessment windows, and keep relief coverage up to date.') }}
                            </p>
                        </div>
                    </li>
                    <li class="grid gap-3 border-t border-[var(--home-sand)] pt-8 md:grid-cols-[4rem_1fr] md:gap-8">
                        <span class="font-display text-2xl font-semibold text-[var(--home-teal)]">04</span>
                        <div>
                            <h3 class="font-display text-lg font-semibold">{{ __('Review and report') }}</h3>
                            <p class="mt-2 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                                {{ __('Publish results, open the reports catalog, download PDF or CSV, and inspect the activity log when changes need tracing.') }}
                            </p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        <section class="border-t border-[var(--home-sand)] bg-[var(--home-ink)] text-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-8 px-6 py-20 lg:flex-row lg:items-end lg:justify-between lg:px-8 lg:py-24">
                <div class="max-w-xl">
                    <h2 class="font-display text-3xl font-semibold tracking-tight">
                        {{ __('Ready when your school day starts') }}
                    </h2>
                    <p class="mt-4 font-reading text-lg leading-relaxed text-white/80">
                        {{ __('Sign in with the account your administrator created. There is no public self-registration — access stays inside the school.') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-md bg-[var(--home-teal)] px-5 py-3 font-display text-sm font-semibold text-white transition hover:bg-[#0c585a]"
                        >
                            {{ __('Go to dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-md bg-[var(--home-teal)] px-5 py-3 font-display text-sm font-semibold text-white transition hover:bg-[#0c585a]"
                        >
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <footer class="border-t border-white/10 bg-[var(--home-ink)] text-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-6 py-8 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p class="font-display text-sm font-semibold">
                    {{ config('app.name', 'SMIS') }}
                </p>
                <p class="text-sm text-white/55">
                    {{ __('Smart School Data Gathering & Management System') }}
                </p>
            </div>
        </footer>
    </body>
</html>
