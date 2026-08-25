<x-layouts::app>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" icon="home" />
                    <flux:breadcrumbs.item :href="route('admin.lessons.index')">{{ __('Video Lessons') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>{{ $lesson->title }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                
                <flux:heading size="xl" class="mt-4">{{ $lesson->title }}</flux:heading>
                <div class="flex items-center gap-2 mt-2">
                    <flux:badge size="sm" variant="pill" color="blue">{{ $lesson->schoolClasses->pluck('code')->join(', ') }}</flux:badge>
                    <flux:badge size="sm" variant="pill" color="zinc">{{ $lesson->subject->name }}</flux:badge>
                    <flux:text size="sm" class="text-gray-500">{{ __('Posted by') }} {{ $lesson->teacher->user->name }} - {{ $lesson->created_at->diffForHumans() }}</flux:text>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this lesson? This action cannot be undone.') }}');">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger" icon="trash">
                        {{ __('Delete Lesson') }}
                    </flux:button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
        <div class="lg:col-span-2 space-y-6">
            @if ($lesson->embed_url)
                <div class="relative w-full pb-[56.25%] overflow-hidden rounded-xl bg-black shadow-lg">
                    <iframe src="{{ $lesson->embed_url }}" class="absolute inset-0 h-full w-full border-0" allowfullscreen></iframe>
                </div>
            @endif

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Homework / Instructions') }}</flux:heading>
                <div class="prose max-w-none dark:prose-invert">
                    {!! $lesson->description ?? __('No description provided.') !!}
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <flux:heading size="md" class="mb-4">{{ __('Lesson Details') }}</flux:heading>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Class') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->schoolClasses->pluck('code')->join(', ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Subject') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->subject->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Teacher') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->teacher->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Created At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                </dl>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
