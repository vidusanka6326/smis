<x-layouts::app :title="__('Activity log')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Activity log') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Audit trail for sensitive actions (users, marks, attendance, publish).') }}</flux:text>
        </div>

        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex flex-wrap items-end gap-3">
            <flux:select name="action" :label="__('Action')" class="min-w-56">
                <flux:select.option value="">{{ __('All actions') }}</flux:select.option>
                @foreach ($actions as $action)
                    <flux:select.option :value="$action->value" :selected="$selectedAction === $action->value">
                        {{ $action->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-3 py-2 text-left">{{ __('When') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Actor') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Action') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('Description') }}</th>
                        <th class="px-3 py-2 text-left">{{ __('IP') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at?->toDateTimeString() }}</td>
                            <td class="px-3 py-2">{{ $log->causer?->name ?? __('System') }}</td>
                            <td class="px-3 py-2">{{ $log->action->label() }}</td>
                            <td class="px-3 py-2">{{ $log->description }}</td>
                            <td class="px-3 py-2">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-zinc-500">{{ __('No activity recorded yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</x-layouts::app>
