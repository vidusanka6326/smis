<section class="w-full">
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    @endpush
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    @endpush

    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name, email address, and profile photo')">
        <div x-data="profileImageCropper()" class="my-6">
            <!-- Profile Photo Upload UI -->
            <div class="mb-6 flex items-center gap-6">
                <!-- Current or Preview Avatar -->
                <div class="relative shrink-0">
                    <template x-if="!previewUrl">
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="Profile Photo" class="size-20 rounded-full object-cover shadow-sm">
                    </template>
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Cropped Preview" class="size-20 rounded-full object-cover shadow-sm ring-2 ring-indigo-500">
                    </template>
                </div>
                
                <div>
                    <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="selectNewPhoto">
                    <flux:button variant="outline" size="sm" @click="$refs.fileInput.click()">
                        {{ __('Select New Photo') }}
                    </flux:button>
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('JPG, GIF or PNG. Max size of 2MB.') }}
                    </p>
                </div>
            </div>

            <!-- Cropper Modal -->
            <flux:modal name="cropperModal" class="min-w-[22rem] md:min-w-[32rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('Crop Profile Photo') }}</flux:heading>
                        <flux:subheading>{{ __('Drag and resize the box to crop your photo.') }}</flux:subheading>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800" style="max-height: 60vh;">
                        <img id="cropperImage" src="" alt="To Crop" class="block max-w-full">
                    </div>

                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button variant="primary" @click="applyCrop">{{ __('Apply Crop') }}</flux:button>
                    </div>
                </div>
            </flux:modal>

            <form wire:submit="updateProfileInformation" class="w-full space-y-6">
                <!-- Hidden input to bind cropped data to Livewire -->
                <input type="hidden" wire:model="croppedPhoto">

                <x-form.grid>
                    <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />
                    <div>
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                        @if ($this->hasUnverifiedEmail)
                            <flux:text class="mt-4">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>
                        @endif
                    </div>
                </x-form.grid>

                <x-form.actions>
                    <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                </x-form.actions>
            </form>
        </div>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('profileImageCropper', () => ({
                cropper: null,
                previewUrl: null,

                selectNewPhoto(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageElement = document.getElementById('cropperImage');
                        imageElement.src = e.target.result;
                        Flux.modal('cropperModal').show();

                        if (this.cropper) {
                            this.cropper.destroy();
                        }
                        
                        // Small delay to ensure modal is visible before initializing cropper
                        setTimeout(() => {
                            this.cropper = new Cropper(imageElement, {
                                aspectRatio: 1,
                                viewMode: 1,
                                autoCropArea: 1,
                            });
                        }, 150);
                    };
                    reader.readAsDataURL(file);
                },

                applyCrop() {
                    if (!this.cropper) return;
                    
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 400,
                        height: 400,
                    });
                    
                    const base64Image = canvas.toDataURL('image/jpeg');
                    
                    // Set preview
                    this.previewUrl = base64Image;
                    
                    // Set to livewire property
                    this.$wire.set('croppedPhoto', base64Image);
                    
                    Flux.modal('cropperModal').close();
                }
            }));
        });
    </script>
</section>
