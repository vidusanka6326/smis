<x-layouts::app :title="__('Mark entry')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Mark entry') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Enter marks per exam subject. Results lock after publish.') }}</flux:text>
        </div>

        <x-list.filters :action="route('admin.marks.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Exam name') }}" />
            <flux:select name="academic_year_id" :label="__('Academic year')" :placeholder="__('All')">
                @foreach ($academicYears as $year)
                    <flux:select.option :value="$year->id" :selected="(string) ($filters['academic_year_id'] ?? '') === (string) $year->id">{{ $year->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="type" :label="__('Type')" :placeholder="__('All')">
                @foreach ($types as $type)
                    <flux:select.option :value="$type->value" :selected="($filters['type'] ?? null) === $type->value">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" :placeholder="__('All')">
                <flux:select.option value="draft" :selected="($filters['status'] ?? null) === 'draft'">{{ __('Draft') }}</flux:select.option>
                <flux:select.option value="published" :selected="($filters['status'] ?? null) === 'published'">{{ __('Published') }}</flux:select.option>
            </flux:select>
        </x-list.filters>

        <div class="space-y-4">
            @forelse ($exams as $exam)
                <div class="rounded-xl border border-border bg-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <flux:heading size="sm">{{ $exam->name }}</flux:heading>
                            <flux:text>{{ $exam->type->label() }} · {{ $exam->isPublished() ? __('Published') : __('Draft') }}</flux:text>
                        </div>
                    </div>
                    <ul class="mt-3 space-y-1 text-sm">
                        @forelse ($exam->examSubjects as $examSubject)
                            <li>
                                {{ $examSubject->subject?->name }}
                                @unless ($exam->isPublished())
                                    — <a class="underline" href="{{ route('admin.marks.edit', $examSubject) }}">{{ __('Enter marks') }}</a>
                                @else
                                    — {{ __('Locked') }}
                                @endunless
                            </li>
                        @empty
                            <li class="text-muted-foreground">{{ __('No subjects configured.') }}</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <p class="rounded-xl border border-border bg-card px-4 py-10 text-center text-muted-foreground">{{ __('No exams match these filters.') }}</p>
            @endforelse
        </div>

        <x-list.pagination :paginator="$exams" />
    </div>
</x-layouts::app>
