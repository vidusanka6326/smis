@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
])

@php
    $invalid ??= ($name && $errors->has($name));

    $buttonSize = match ($size) {
        'sm', 'xs' => $size,
        default => 'base',
    };

    $buttonClass = $invalid ? 'w-full border-red-500' : 'w-full';
@endphp

<div
    {{ $attributes->only('class')->class('relative w-full') }}
    data-flux-select
    x-data="{
        options: [],
        value: '',
        placeholder: {{ \Illuminate\Support\Js::from($placeholder ?? '') }},
        get label() {
            const selected = this.options.find((option) => String(option.value) === String(this.value));

            if (! selected) {
                return this.placeholder;
            }

            return selected.label;
        },
        get isPlaceholder() {
            const selected = this.options.find((option) => String(option.value) === String(this.value));

            return ! selected || selected.placeholder;
        },
        init() {
            this.$nextTick(() => this.syncFromNative());
        },
        syncFromNative() {
            const select = this.$refs.native;

            if (! select) {
                return;
            }

            this.options = Array.from(select.options).map((option) => ({
                value: option.value,
                label: option.textContent.trim(),
                disabled: option.disabled,
                placeholder: option.classList.contains('placeholder'),
            }));
            this.value = select.value;
        },
        choose(option) {
            if (option.disabled) {
                return;
            }

            const select = this.$refs.native;
            select.value = option.value;
            this.value = option.value;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }"
>
    <select
        x-ref="native"
        {{ $attributes->except('class')->class('sr-only pointer-events-none') }}
        tabindex="-1"
        aria-hidden="true"
        @if ($invalid) aria-invalid="true" data-invalid @endif
        @isset ($name) name="{{ $name }}" @endisset
        @if (is_numeric($size)) size="{{ $size }}" @endif
        data-flux-control
        data-flux-select-native
        data-flux-group-target
    >
        <?php if ($placeholder): ?>
            <option value="" disabled selected class="placeholder">{{ $placeholder }}</option>
        <?php endif; ?>

        {{ $slot }}
    </select>

    <flux:dropdown class="w-full" position="bottom" align="start">
        <flux:button
            type="button"
            variant="outline"
            align="start"
            :size="$buttonSize"
            :class="$buttonClass"
            icon:trailing="chevron-down"
            x-bind:disabled="$refs.native && $refs.native.disabled"
        >
            <span
                class="min-w-0 flex-1 truncate text-start font-normal"
                x-bind:class="isPlaceholder && 'text-zinc-400 dark:text-zinc-400'"
                x-text="label"
            ></span>
        </flux:button>

        <flux:menu class="max-h-60 min-w-48 overflow-y-auto">
            <template x-for="(option, index) in options" :key="index">
                <flux:menu.item
                    x-on:click="choose(option)"
                    x-bind:disabled="option.disabled"
                >
                    <span class="flex min-w-0 flex-1 items-center gap-2">
                        <span
                            class="inline-flex size-4 shrink-0 items-center justify-center"
                            x-show="String(value) === String(option.value) && ! option.placeholder"
                            x-cloak
                        >
                            <flux:icon icon="check" variant="micro" />
                        </span>
                        <span
                            class="inline-flex size-4 shrink-0"
                            x-show="String(value) !== String(option.value) || option.placeholder"
                            x-cloak
                        ></span>
                        <span class="truncate" x-text="option.label"></span>
                    </span>
                </flux:menu.item>
            </template>
        </flux:menu>
    </flux:dropdown>
</div>
