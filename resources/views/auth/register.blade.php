<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create Account</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Join our admin dashboard</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input 
                label="{{ __('Full Name') }}"
                placeholder="Enter your full name"
                name="name"
                type="text"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />
        </div>

        <!-- Email Address -->
        <div>
            <x-input 
                label="{{ __('Email Address') }}"
                placeholder="Enter your email"
                name="email"
                type="email"
                :value="old('email')"
                required
                autocomplete="username"
            />
        </div>

        <!-- Password -->
        <div>
            <x-input 
                label="{{ __('Password') }}"
                placeholder="Create a password"
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
                placeholder="Confirm your password"
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
                <x-icon name="user-plus" class="w-5 h-5" />
            </x-slot>
            {{ __('Create Account') }}
        </x-button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" 
                   class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-medium transition-colors">
                    {{ __('Sign in here') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
