<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Enter your new password</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input 
                label="{{ __('Email Address') }}"
                name="email"
                type="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
                readonly
            />
        </div>

        <!-- Password -->
        <div>
            <x-input 
                label="{{ __('New Password') }}"
                placeholder="Enter your new password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
            />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input 
                label="{{ __('Confirm Password') }}"
                placeholder="Confirm your new password"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
            />
        </div>

        <!-- Submit Button -->
        <x-button 
            type="submit" 
            class="w-full" 
            primary 
            xl
        >
            <x-slot name="prepend">
                <x-icon name="key" class="w-5 h-5" />
            </x-slot>
            {{ __('Update Password') }}
        </x-button>

        <!-- Back to Login -->
        <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('login') }}" 
               class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-medium transition-colors">
                {{ __('← Back to Login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
