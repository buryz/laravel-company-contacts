<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Rejestracja</h2>
        <p class="text-sm text-gray-600 mt-2">Utwórz nowe konto w systemie zarządzania kontaktami</p>
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

    <form method="POST" action="{{ route('auth.register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nazwa użytkownika" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Jan Kowalski" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" value="Adres email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="twoj@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Hasło" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" 
                            placeholder="Minimum 8 znaków" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Potwierdź hasło" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" 
                            placeholder="Powtórz hasło" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-blue-600 hover:text-blue-800 underline" href="{{ route('contacts.index') }}">
                ← Powrót do kontaktów
            </a>

            <div class="flex items-center space-x-4">
                <a class="text-sm text-gray-600 hover:text-gray-800 underline" href="{{ route('auth.login') }}">
                    Masz już konto?
                </a>
                <x-primary-button>
                    Zarejestruj się
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>