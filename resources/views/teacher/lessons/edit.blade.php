<x-layouts::app>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('teacher.dashboard')" icon="home" />
            <flux:breadcrumbs.item :href="route('teacher.lessons.index')">{{ __('Video Lessons') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('Edit Lesson') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        
        <flux:heading size="xl" class="mt-4">{{ __('Edit Video Lesson') }}</flux:heading>
        <flux:subheading>{{ __('Update video lesson details and homework') }}</flux:subheading>
    </div>

    @push('styles')
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <style>
            trix-toolbar [data-trix-button-group="file-tools"] {
                display: none;
            }
            .trix-content {
                min-height: 200px;
            }
        </style>
    @endpush

    @push('scripts')
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
        <script>
            document.addEventListener("trix-file-accept", function(event) {
                event.preventDefault();
            });
        </script>
    @endpush

    <form method="POST" action="{{ route('teacher.lessons.update', $lesson) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <x-form.section :title="__('Lesson Details')" :description="__('Provide the video and homework instructions for this lesson.')">
            <x-form.grid>
                <flux:input name="title" :label="__('Title')" :value="old('title', $lesson->title)" required autofocus class="sm:col-span-2" />
                
                <flux:select name="school_class_ids[]" :label="__('Classes')" multiple required>
                    @foreach ($classes as $class)
                        <flux:select.option :value="$class->id" :selected="in_array($class->id, old('school_class_ids', $lesson->schoolClasses->pluck('id')->toArray()))">{{ $class->code }}</flux:select.option>
                    @endforeach
                </flux:select>
                
                <flux:select name="subject_id" :label="__('Subject')" required>
                    <flux:select.option value="" disabled>{{ __('Select a subject') }}</flux:select.option>
                    @foreach ($subjects as $subject)
                        <flux:select.option :value="$subject->id" :selected="old('subject_id', $lesson->subject_id) == $subject->id">{{ $subject->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input name="youtube_url" type="url" :label="__('YouTube URL')" :value="old('youtube_url', $lesson->youtube_url)" placeholder="https://www.youtube.com/watch?v=..." class="sm:col-span-2" />
                
                <div class="sm:col-span-2">
                    <flux:label>{{ __('Homework / Description') }}</flux:label>
                    <div class="mt-2" wire:ignore>
                        <input id="description_input" type="hidden" name="description" value="{{ old('description', $lesson->description) }}">
                        <trix-editor input="description_input" class="trix-content prose max-w-none rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></trix-editor>
                    </div>
                    @error('description')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>
            </x-form.grid>
        </x-form.section>

        <div class="flex items-center justify-end gap-x-4 border-t border-gray-900/10 dark:border-gray-100/10 pt-6">
            <flux:button :href="route('teacher.lessons.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Update Lesson') }}</flux:button>
        </div>
    </form>
</x-layouts::app>
