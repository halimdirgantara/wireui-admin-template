<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome Back</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Sign in to your admin account</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input 
                label="{{ __('Email Address') }}"
                placeholder="Enter your email"
                name="email"
                type="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
        </div>

        <!-- Password -->
        <div>
            <x-password 
                label="{{ __('Password') }}"
                placeholder="Enter your password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
            />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <x-checkbox 
                id="remember_me"
                name="remember"
                label="{{ __('Remember me') }}"
            />

            @if (Route::has('password.request'))
                <a class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 transition-colors" 
                   href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <x-button 
            type="submit" 
            class="w-full" 
            primary 
            xl
        >
            <x-slot name="prepend">
                <x-icon name="arrow-left-end-on-rectangle" class="w-5 h-5" />
            </x-slot>
            {{ __('Sign In') }}
        </x-button>

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" 
                       class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-medium transition-colors">
                        {{ __('Create one here') }}
                    </a>
                </p>
            </div>
        @endif
    </form>
</x-guest-layout>
