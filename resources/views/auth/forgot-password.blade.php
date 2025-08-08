<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reset Password</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Enter your email to receive a reset link</p>
    </div>

    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg text-blue-700 dark:text-blue-300 text-sm">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

        <!-- Submit Button -->
        <x-button 
            type="submit" 
            class="w-full" 
            primary 
            xl
            icon="envelope"
        >
            {{ __('Send Reset Link') }}
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
