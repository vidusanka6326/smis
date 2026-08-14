<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-background text-foreground">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-sidebar-border bg-sidebar">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs(['dashboard', 'admin.dashboard', 'teacher.dashboard', 'student.dashboard'])" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @role('admin|officer|teacher')
                        <flux:sidebar.item icon="bot" :href="route('agent.chat')" :current="request()->routeIs('agent.*')" wire:navigate>
                            {{ __('SMIS Agent') }}
                        </flux:sidebar.item>
                    @endrole

                    @role('admin|officer')
                        @role('admin')
                            <flux:sidebar.item icon="briefcase" :href="route('admin.officers.index')" :current="request()->routeIs('admin.officers.*')" wire:navigate>
                                {{ __('Officers') }}
                            </flux:sidebar.item>
                        @endrole
                        <flux:sidebar.item icon="academic-cap" :href="route('admin.teachers.index')" :current="request()->routeIs('admin.teachers.*')" wire:navigate>
                            {{ __('Teachers') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.students.index')" :current="request()->routeIs('admin.students.*')" wire:navigate>
                            {{ __('Students') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('admin.academic-years.index')" :current="request()->routeIs('admin.academic-years.*')" wire:navigate>
                            {{ __('Academic years') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="queue-list" :href="route('admin.grades.index')" :current="request()->routeIs('admin.grades.*')" wire:navigate>
                            {{ __('Grades') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="squares-2x2" :href="route('admin.streams.index')" :current="request()->routeIs('admin.streams.*')" wire:navigate>
                            {{ __('Streams') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="book-open" :href="route('admin.subjects.index')" :current="request()->routeIs('admin.subjects.*')" wire:navigate>
                            {{ __('Subjects') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-library" :href="route('admin.classes.index')" :current="request()->routeIs('admin.classes.*')" wire:navigate>
                            {{ __('Classes') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('admin.timetables.index')" :current="request()->routeIs('admin.timetables.*')" wire:navigate>
                            {{ __('Timetables') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrows-right-left" :href="route('admin.relief-assignments.index')" :current="request()->routeIs('admin.relief-assignments.*')" wire:navigate>
                            {{ __('Relief') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('admin.attendance.sessions.index')" :current="request()->routeIs('admin.attendance.*')" wire:navigate>
                            {{ __('Attendance') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('admin.exams.index')" :current="request()->routeIs(['admin.exams.*', 'admin.marks.*'])" wire:navigate>
                            {{ __('Exams') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('admin.reports.dashboard')" :current="request()->routeIs('admin.reports.*')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.activity-logs.index')" :current="request()->routeIs('admin.activity-logs.*')" wire:navigate>
                            {{ __('Activity log') }}
                        </flux:sidebar.item>
                    @endrole

                    @role('teacher')
                        <flux:sidebar.item icon="users" :href="route('teacher.students.index')" :current="request()->routeIs('teacher.students.*')" wire:navigate>
                            {{ __('My students') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="table-cells" :href="route('teacher.timetable')" :current="request()->routeIs('teacher.timetable')" wire:navigate>
                            {{ __('My timetable') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('teacher.attendance.sessions.index')" :current="request()->routeIs('teacher.attendance.*')" wire:navigate>
                            {{ __('Attendance') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('teacher.marks.index')" :current="request()->routeIs('teacher.marks.*')" wire:navigate>
                            {{ __('Marks') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('teacher.reports.dashboard')" :current="request()->routeIs('teacher.reports.*')" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                    @endrole

                    @role('student')
                        <flux:sidebar.item icon="table-cells" :href="route('student.timetable')" :current="request()->routeIs('student.timetable')" wire:navigate>
                            {{ __('My timetable') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-check" :href="route('student.attendance')" :current="request()->routeIs('student.attendance')" wire:navigate>
                            {{ __('My attendance') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="document-text" :href="route('student.results')" :current="request()->routeIs('student.results')" wire:navigate>
                            {{ __('My results') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-bar" :href="route('student.reports')" :current="request()->routeIs(['student.reports', 'student.reports.*', 'student.report'])" wire:navigate>
                            {{ __('Reports') }}
                        </flux:sidebar.item>
                    @endrole
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
