<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ __('Smart School Data Gathering & Management System — attendance, timetables, examinations, and reports in one place.') }}">

        <title>{{ config('app.name', 'SMIS') }} — {{ __('Smart School Management') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
                <a href="{{ route('home') }}" class="font-display text-lg font-semibold tracking-tight text-white">
                    {{ config('app.name', 'SMIS') }}
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-3">
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
                <p class="home-rise font-display text-5xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                    {{ config('app.name', 'SMIS') }}
                </p>

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
                </div>
            </div>
        </section>

        <section class="border-t border-[var(--home-sand)] bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-[var(--home-ink)]">
                    {{ __('What SMIS runs every day') }}
                </h2>
                <p class="mt-3 max-w-2xl font-reading text-lg text-[var(--home-ink)]/75">
                    {{ __('Role-based access keeps each school workflow clear — from class attendance to published exam results.') }}
                </p>

                <div class="mt-14 grid gap-10 md:grid-cols-3 md:gap-12">
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Attendance') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Capture student and teacher attendance, then review monthly summaries by class.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Timetables') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Build weekly period grids, spot conflicts early, and assign relief teachers when needed.') }}
                        </p>
                    </div>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.14em] text-[var(--home-teal)]">{{ __('Exams & reports') }}</p>
                        <p class="mt-3 font-reading text-base leading-relaxed text-[var(--home-ink)]/80">
                            {{ __('Enter marks with grade letters, publish results safely, and explore school-wide analytics.') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-[var(--home-sand)] bg-[var(--home-mist)]">
            <div class="mx-auto flex max-w-6xl flex-col gap-3 px-6 py-8 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p class="font-display text-sm font-semibold text-[var(--home-ink)]">
                    {{ config('app.name', 'SMIS') }}
                </p>
                <p class="text-sm text-[var(--home-ink)]/60">
                    {{ __('Smart School Data Gathering & Management System') }}
                </p>
            </div>
        </footer>
    </body>
</html>
