<x-layouts::app :title="__('Streams')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Streams') }}</flux:heading>
                <flux:text class="mt-1">{{ __('A/L streams for grades 12–13 (Science, Commerce, Arts, Technology).') }}</flux:text>
            </div>
            <flux:button :href="route('admin.streams.create')" variant="primary" wire:navigate>{{ __('Add stream') }}</flux:button>
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
                        <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($streams as $stream)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3">{{ $stream->name }}</td>
                            <td class="px-4 py-3">{{ $stream->code }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <flux:button size="sm" :href="route('admin.streams.edit', $stream)" variant="ghost" wire:navigate>{{ __('Edit') }}</flux:button>
                                    <form method="POST" action="{{ route('admin.streams.destroy', $stream) }}" onsubmit="return confirm(@js(__('Delete this stream?')))">
                                        @csrf
                                        @method('DELETE')
                                        <flux:button size="sm" type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td colspan="3" class="px-4 py-6 text-zinc-500">{{ __('No streams yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $streams->links() }}
    </div>
</x-layouts::app>
