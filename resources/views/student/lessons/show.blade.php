<x-layouts::app>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('student.dashboard')" icon="home" />
            <flux:breadcrumbs.item :href="route('student.lessons.index')">{{ __('Video Lessons') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $lesson->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        
        <flux:heading size="xl" class="mt-4">{{ $lesson->title }}</flux:heading>
        <div class="flex items-center gap-2 mt-2">
            <flux:badge size="sm" variant="pill" color="blue">{{ $lesson->subject->name }}</flux:badge>
            <flux:text size="sm" class="text-gray-500">{{ __('Teacher:') }} {{ $lesson->teacher->user->name }} - {{ $lesson->created_at->diffForHumans() }}</flux:text>
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
                    {!! $lesson->description ?? __('No homework instructions provided for this lesson.') !!}
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <flux:heading size="md" class="mb-4">{{ __('Lesson Details') }}</flux:heading>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Subject') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->subject->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Teacher') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->teacher->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Posted') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $lesson->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
