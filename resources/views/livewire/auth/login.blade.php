<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Select your role and enter your credentials to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Role Selector -->
            <div>
                <p class="mb-3 text-center text-sm font-semibold text-foreground">{{ __('Select Your Role') }}</p>
                <div class="grid grid-cols-2 gap-3" id="role-selector">
                    @php
                        $roles = [
                            ['value' => 'admin',   'label' => 'Admin',   'icon' => 'shield-check'],
                            ['value' => 'officer', 'label' => 'Officer', 'icon' => 'briefcase'],
                            ['value' => 'teacher', 'label' => 'Teacher', 'icon' => 'academic-cap'],
                            ['value' => 'student', 'label' => 'Student', 'icon' => 'user'],
                        ];
                        $selectedRole = old('role', 'student');
                    @endphp

                    @foreach ($roles as $role)
                        <label
                            for="role-{{ $role['value'] }}"
                            class="role-card flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 px-3 py-4 text-center transition-all duration-200
                                {{ $selectedRole === $role['value']
                                    ? 'border-primary bg-secondary text-primary shadow-sm'
                                    : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:bg-secondary/60' }}"
                        >
                            <input
                                type="radio"
                                name="role"
                                id="role-{{ $role['value'] }}"
                                value="{{ $role['value'] }}"
                                class="sr-only"
                                {{ $selectedRole === $role['value'] ? 'checked' : '' }}
                                onchange="updateRoleCards(this)"
                            >
                            <flux:icon name="{{ $role['icon'] }}" class="size-7" />
                            <span class="text-sm font-semibold">{{ $role['label'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('role')
                    <flux:error name="role" />
                @enderror
            </div>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>

<script>
    function updateRoleCards(radio) {
        document.querySelectorAll('.role-card').forEach(function (label) {
            const active = label.htmlFor === radio.id;
            // active state — light teal bg, dark teal border + text
            label.classList.toggle('border-primary', active);
            label.classList.toggle('bg-secondary', active);
            label.classList.toggle('text-primary', active);
            label.classList.toggle('shadow-sm', active);
            // inactive state
            label.classList.toggle('border-border', !active);
            label.classList.toggle('bg-card', !active);
            label.classList.toggle('text-muted-foreground', !active);
            label.classList.toggle('hover:border-primary/40', !active);
            label.classList.toggle('hover:bg-secondary/60', !active);
        });
    }
</script>
