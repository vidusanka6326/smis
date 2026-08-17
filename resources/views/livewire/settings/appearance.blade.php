<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div class="space-y-8">
            <div>
                <flux:heading size="sm" class="mb-3">{{ __('Language') }}</flux:heading>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Enums\AppLocale::cases() as $locale)
                        <form method="POST" action="{{ route('locale.update') }}">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $locale->value }}">
                            <flux:button
                                type="submit"
                                :variant="\App\Enums\AppLocale::current() === $locale ? 'primary' : 'filled'"
                            >
                                {{ $locale->nativeName() }}
                            </flux:button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div>
                <flux:heading size="sm" class="mb-3">{{ __('Appearance') }}</flux:heading>
                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                    <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
                </flux:radio.group>
            </div>
        </div>
    </x-settings.layout>
</section>
