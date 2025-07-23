<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Logowanie</h2>
        <p class="text-sm text-gray-600 mt-2">Zaloguj się do systemu zarządzania kontaktami</p>
    </div>

    <!-- Session Status -->
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('auth.login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Adres email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="twoj@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Hasło" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="Wprowadź hasło" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Zapamiętaj mnie</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-blue-600 hover:text-blue-800 underline" href="{{ route('contacts.index') }}">
                ← Powrót do kontaktów
            </a>

            <div class="flex items-center space-x-4">
                <a class="text-sm text-gray-600 hover:text-gray-800 underline" href="{{ route('auth.register') }}">
                    Nie masz konta?
                </a>
                <x-primary-button>
                    Zaloguj się
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>