<x-layouts::app :title="__('My attendance')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('My attendance') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Record your own daily attendance.') }}</flux:text>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        <form method="POST" action="{{ route('teacher.attendance.self.store') }}" class="grid gap-3 rounded-xl border border-zinc-200 p-4 md:grid-cols-3 dark:border-zinc-700">
            @csrf
            <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
            <flux:input type="date" name="date" :label="__('Date')" :value="now()->toDateString()" />
            <flux:select name="status" :label="__('Status')">
                @foreach ($statuses as $status)
                    <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex items-end">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('Date') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2">{{ $record->date->toDateString() }}</td>
                            <td class="px-3 py-2">{{ $record->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-3 py-6 text-zinc-500">{{ __('No records yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links() }}
    </div>
</x-layouts::app>
