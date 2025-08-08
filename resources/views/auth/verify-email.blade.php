<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Verify Email</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Check your inbox to complete registration</p>
    </div>

    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg text-blue-700 dark:text-blue-300 text-sm">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 text-sm">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button 
                type="submit" 
                class="w-full" 
                primary 
                xl
                icon="envelope"
            >
                {{ __('Resend Verification Email') }}
            </x-button>
        </form>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-button 
                    type="submit" 
                    class="w-full" 
                    secondary 
                    outline
                    icon="arrow-right-on-rectangle"
                >
                    {{ __('Log Out') }}
                </x-button>
            </form>
        </div>
    </div>
</x-guest-layout>
