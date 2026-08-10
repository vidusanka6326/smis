<x-layouts::app :title="__('Academic years')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Academic years') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Configure school academic years and mark the current year.') }}</flux:text>
            </div>
            <flux:button :href="route('admin.academic-years.create')" variant="primary" wire:navigate>
                {{ __('Add academic year') }}
            </flux:button>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
            </flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle">
                <flux:callout.heading>{{ $errors->first() }}</flux:callout.heading>
            </flux:callout>
        @endif

        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full text-sm">
                <thead class="bg-zinc-50 text-left dark:bg-zinc-900">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Starts') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Ends') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Current') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($academicYears as $academicYear)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $academicYear->name }}</td>
                            <td class="px-4 py-3">{{ $academicYear->starts_on->toDateString() }}</td>
                            <td class="px-4 py-3">{{ $academicYear->ends_on->toDateString() }}</td>
                            <td class="px-4 py-3">{{ $academicYear->is_current ? __('Yes') : __('No') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" :href="route('admin.academic-years.edit', $academicYear)" variant="ghost" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:button>
                                    <form method="POST" action="{{ route('admin.academic-years.destroy', $academicYear) }}" onsubmit="return confirm(@js(__('Delete this academic year?')))">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="5" class="px-4 py-6 text-zinc-500">{{ __('No academic years yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $academicYears->links() }}
    </div>
</x-layouts::app>
