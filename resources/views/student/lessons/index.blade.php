<x-layouts::app>
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Video Lessons') }}</flux:heading>
        <flux:subheading>{{ __('Watch video lessons and complete your homework') }}</flux:subheading>
    </div>

    @if($lessons->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 py-12 text-center dark:border-gray-700">
            <flux:icon icon="video-camera" class="mx-auto h-12 w-12 text-gray-400" />
            <flux:heading size="lg" class="mt-4">{{ __('No lessons found') }}</flux:heading>
            <flux:subheading class="mt-2">{{ __('There are no video lessons available for your class yet.') }}</flux:subheading>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($lessons as $lesson)
                <flux:card class="flex flex-col h-full hover:shadow-lg transition-shadow">
                    <div class="flex-1">
                        @if ($lesson->embed_url)
                            <div class="relative w-full pb-[56.25%] mb-4 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800">
                                <iframe src="{{ $lesson->embed_url }}" class="absolute inset-0 h-full w-full border-0" allowfullscreen></iframe>
                            </div>
                        @else
                            <div class="relative w-full pb-[56.25%] mb-4 flex items-center justify-center overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800">
                                <flux:icon icon="video-camera-slash" class="size-8 text-gray-400" />
                            </div>
                        @endif

                        <flux:heading size="lg" class="mb-1">{{ $lesson->title }}</flux:heading>
                        
                        <div class="flex items-center gap-2 mb-2">
                            <flux:badge size="sm" variant="pill" color="blue">{{ $lesson->subject->name }}</flux:badge>
                            <flux:badge size="sm" variant="pill" color="orange">{{ $lesson->teacher->user->name }}</flux:badge>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-800 pt-4">
                        <flux:text size="sm" class="text-gray-500">{{ $lesson->created_at->diffForHumans() }}</flux:text>
                        <flux:button :href="route('student.lessons.show', $lesson)" size="sm" variant="primary" wire:navigate>
                            {{ __('View Lesson') }}
                        </flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $lessons->links() }}
        </div>
    @endif
</x-layouts::app>
