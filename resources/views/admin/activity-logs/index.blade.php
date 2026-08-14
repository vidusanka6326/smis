<x-layouts::app :title="__('Activity log')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Activity log') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Audit trail for sensitive actions (users, marks, attendance, publish).') }}</flux:text>
        </div>

        <x-list.filters :action="route('admin.activity-logs.index')" :filters="$filters">
            <flux:input name="search" :label="__('Search')" :value="$filters['search'] ?? ''" placeholder="{{ __('Description, actor, or IP') }}" />
            <flux:select name="action" :label="__('Action')" :placeholder="__('All actions')">
                @foreach ($actions as $action)
                    <flux:select.option :value="$action->value" :selected="($filters['action'] ?? null) === $action->value">
                        {{ $action->label() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            <flux:input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
        </x-list.filters>

        <x-list.table>
            <x-slot:head>
                <th class="px-4 py-3 font-medium">{{ __('When') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Actor') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Action') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                <th class="px-4 py-3 font-medium">{{ __('IP') }}</th>
            </x-slot:head>
            @forelse ($logs as $log)
                <tr class="border-t border-border">
                    <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->toDateTimeString() }}</td>
                    <td class="px-4 py-3">{{ $log->causer?->name ?? __('System') }}</td>
                    <td class="px-4 py-3">{{ $log->action->label() }}</td>
                    <td class="px-4 py-3">{{ $log->description }}</td>
                    <td class="px-4 py-3">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr class="border-t border-border">
                    <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">{{ __('No activity matches these filters.') }}</td>
                </tr>
            @endforelse
        </x-list.table>

        <x-list.pagination :paginator="$logs" />
    </div>
</x-layouts::app>
