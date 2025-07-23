<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Książka Kontaktów Firmowych' }} - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md shadow-soft border-b border-gray-200/50 sticky top-0 z-40" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and main navigation -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="{{ route('contacts.index') }}" class="flex items-center group">
                                <div class="relative">
                                    <svg class="h-8 w-8 text-primary-600 group-hover:text-primary-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div class="absolute -inset-1 bg-primary-600/20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 blur-sm"></div>
                                </div>
                                <span class="ml-3 text-xl font-bold text-gray-900 group-hover:text-primary-700 transition-colors duration-200">
                                    Książka Kontaktów
                                </span>
                            </a>
                        </div>
                        
                        <!-- Main navigation links -->
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="{{ route('contacts.index') }}" 
                               class="nav-link {{ request()->routeIs('contacts.index') ? 'nav-link-active' : '' }}">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Wszystkie kontakty
                            </a>
                            @auth
                                <a href="{{ route('contacts.create') }}" 
                                   class="nav-link {{ request()->routeIs('contacts.create') ? 'nav-link-active' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Dodaj kontakt
                                </a>
                                <a href="{{ route('tags.index') }}" 
                                   class="nav-link {{ request()->routeIs('tags.*') ? 'nav-link-active' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Zarządzaj tagami
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 transition-colors duration-200">
                            <svg class="h-6 w-6" :class="{ 'hidden': mobileMenuOpen, 'block': !mobileMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg class="h-6 w-6" :class="{ 'block': mobileMenuOpen, 'hidden': !mobileMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Authentication section -->
                    <div class="hidden md:flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2 px-3 py-1 bg-primary-50 rounded-full">
                                    <div class="w-2 h-2 bg-success-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm text-gray-700">
                                        Witaj, <span class="font-semibold text-primary-700">{{ auth()->user()->name }}</span>
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('auth.logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn-danger text-sm hover-lift">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Wyloguj się
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('auth.login') }}" 
                                   class="btn-ghost">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Zaloguj się
                                </a>
                                <a href="{{ route('auth.register') }}" 
                                   class="btn-primary hover-lift">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Zarejestruj się
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden" x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t border-gray-200">
                    <a href="{{ route('contacts.index') }}" 
                       class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200 {{ request()->routeIs('contacts.index') ? 'bg-primary-50 text-primary-700' : '' }}">
                        <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Wszystkie kontakty
                    </a>
                    @auth
                        <a href="{{ route('contacts.create') }}" 
                           class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200 {{ request()->routeIs('contacts.create') ? 'bg-primary-50 text-primary-700' : '' }}">
                            <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Dodaj kontakt
                        </a>
                        <a href="{{ route('tags.index') }}" 
                           class="text-gray-600 hover:text-gray-900 hover:bg-gray-50 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200 {{ request()->routeIs('tags.*') ? 'bg-primary-50 text-primary-700' : '' }}">
                            <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Zarządzaj tagami
                        </a>
                        
                        <!-- Mobile auth section -->
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="flex items-center px-3 py-2">
                                <div class="w-2 h-2 bg-success-500 rounded-full animate-pulse mr-2"></div>
                                <span class="text-sm text-gray-700">
                                    Witaj, <span class="font-semibold text-primary-700">{{ auth()->user()->name }}</span>
                                </span>
                            </div>
                            <form method="POST" action="{{ route('auth.logout') }}" class="px-3">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left text-danger-600 hover:text-danger-900 hover:bg-danger-50 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Wyloguj się
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="border-t border-gray-200 pt-4 mt-4 space-y-2 px-3">
                            <a href="{{ route('auth.login') }}" 
                               class="w-full btn-ghost justify-start">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Zaloguj się
                            </a>
                            <a href="{{ route('auth.register') }}" 
                               class="w-full btn-primary justify-start">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Zarejestruj się
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        @if (isset($header))
            <header class="bg-white/60 backdrop-blur-sm shadow-soft border-b border-gray-200/50">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 animate-fade-in">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-4">
            @if (session('success'))
                <div class="alert-success animate-slide-up" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="block sm:inline">{{ session('success') }}</span>
                        <button @click="show = false" class="ml-auto text-success-600 hover:text-success-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error animate-slide-up" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="block sm:inline">{{ session('error') }}</span>
                        <button @click="show = false" class="ml-auto text-danger-600 hover:text-danger-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert-warning animate-slide-up" x-data="{ show: true }" x-show="show" x-transition>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="block sm:inline">{{ session('warning') }}</span>
                        <button @click="show = false" class="ml-auto text-warning-600 hover:text-warning-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 animate-fade-in">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white/60 backdrop-blur-sm border-t border-gray-200/50 mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>&copy; {{ date('Y') }} Książka Kontaktów Firmowych</span>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span>Zbudowane z ❤️ używając Laravel & Tailwind CSS</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Contact Modal for QR Code Display -->
    <div id="contactModal" class="modal-backdrop hidden" x-data="{ show: false }" x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="modal-container modal-container-lg animate-bounce-in" @click.away="closeContactModal()">
            <div class="relative">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-primary rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Wizytówka kontaktu</h3>
                            <p class="text-sm text-gray-500">Szczegóły kontaktu i kod QR</p>
                        </div>
                    </div>
                    <button onclick="closeContactModal()" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all duration-200 hover-lift">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div id="modalContent" class="animate-fade-in">
                    <!-- Content will be loaded here -->
                </div>
                
                <!-- Loading State -->
                <div id="modalLoading" class="hidden text-center py-12">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-500">Ładowanie danych kontaktu...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openContactModal(contactId) {
            // Show modal and loading state
            const modal = document.getElementById('contactModal');
            const modalContent = document.getElementById('modalContent');
            const modalLoading = document.getElementById('modalLoading');
            
            modal.classList.remove('hidden');
            modalContent.classList.add('hidden');
            modalLoading.classList.remove('hidden');
            
            fetch(`/contacts/${contactId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showNotification('Wystąpił błąd podczas pobierania danych kontaktu.', 'error');
                    closeContactModal();
                    return;
                }
                
                const contact = data.contact;
                const vcard = data.vcard;
                
                document.getElementById('modalTitle').textContent = `${contact.first_name} ${contact.last_name}`;
                
                let tagsHtml = '';
                if (contact.tags && contact.tags.length > 0) {
                    tagsHtml = `
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Tagi
                            </h4>
                            <div class="flex flex-wrap gap-2">
                    `;
                    contact.tags.forEach(tag => {
                        tagsHtml += `<span class="tag hover-lift" style="background-color: ${tag.color}20; color: ${tag.color}; border: 1px solid ${tag.color}40;">${tag.name}</span>`;
                    });
                    tagsHtml += '</div></div>';
                }
                
                modalContent.innerHTML = `
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Contact Information -->
                            <div class="space-y-6">
                                <div class="card-soft">
                                    <div class="card-body">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Informacje kontaktowe
                                        </h4>
                                        <div class="space-y-4">
                                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-bold text-primary-700">${contact.first_name.charAt(0)}${contact.last_name.charAt(0)}</span>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900">${contact.first_name} ${contact.last_name}</p>
                                                    <p class="text-sm text-gray-600">${contact.position}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-3">
                                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <a href="mailto:${contact.email}" class="text-primary-600 hover:text-primary-800 font-medium">${contact.email}</a>
                                                </div>
                                                
                                                ${contact.phone ? `
                                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                    <a href="tel:${contact.phone}" class="text-primary-600 hover:text-primary-800 font-medium">${contact.phone}</a>
                                                </div>
                                                ` : ''}
                                                
                                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                    </svg>
                                                    <span class="text-gray-700 font-medium">${contact.company}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                ${tagsHtml}
                            </div>
                            
                            <!-- QR Code Section -->
                            <div class="text-center">
                                <div class="card-soft">
                                    <div class="card-body">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 16h4.01M12 8h4.01M12 16h.01m0 4h4.01M20 12h.01m0 8h.01M8 12h.01M8 8h.01M8 16h.01m0 4h4.01M4 12h.01m0 8h.01m0-16h.01m0 4h.01m0 4h.01"></path>
                                            </svg>
                                            Kod QR
                                        </h4>
                                        <div id="qrcode" class="flex justify-center mb-4 p-4 bg-white rounded-lg border-2 border-dashed border-gray-300"></div>
                                        <p class="text-sm text-gray-600 mb-4">Zeskanuj kod QR, aby dodać kontakt do telefonu</p>
                                        <div class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg">
                                            <p class="font-medium mb-1">Instrukcja:</p>
                                            <p>1. Otwórz aplikację aparatu w telefonie</p>
                                            <p>2. Skieruj aparat na kod QR</p>
                                            <p>3. Dotknij powiadomienia, aby dodać kontakt</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                            <button onclick="downloadVCard('${contact.first_name}_${contact.last_name}', \`${vcard.replace(/`/g, '\\`')}\`)" 
                                    class="btn-primary hover-lift">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Pobierz vCard
                            </button>
                            <button onclick="shareContact('${contact.first_name} ${contact.last_name}', '${contact.email}', '${contact.phone || ''}', '${contact.company}')" 
                                    class="btn-secondary hover-lift">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                Udostępnij
                            </button>
                            <button onclick="closeContactModal()" 
                                    class="btn-outline hover-lift">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Zamknij
                            </button>
                        </div>
                    </div>
                `;
                
                // Generate QR code
                generateQRCode(vcard);
                
                // Hide loading and show content
                modalLoading.classList.add('hidden');
                modalContent.classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Wystąpił błąd podczas pobierania danych kontaktu.', 'error');
                closeContactModal();
            });
        }
        
        function closeContactModal() {
            const modal = document.getElementById('contactModal');
            modal.classList.add('hidden');
        }
        
        function generateQRCode(vcard) {
            // Enhanced QR code generation with better styling
            const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=png&margin=10&data=${encodeURIComponent(vcard)}`;
            document.getElementById('qrcode').innerHTML = `
                <div class="inline-block p-2 bg-white rounded-lg shadow-soft">
                    <img src="${qrCodeUrl}" alt="QR Code" class="mx-auto rounded" style="image-rendering: pixelated;">
                </div>
            `;
        }
        
        function downloadVCard(name, vcard) {
            try {
                const blob = new Blob([vcard], { type: 'text/vcard;charset=utf-8' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${name.replace(/[^a-z0-9]/gi, '_')}.vcf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                showNotification('Plik vCard został pobrany pomyślnie!', 'success');
            } catch (error) {
                console.error('Error downloading vCard:', error);
                showNotification('Wystąpił błąd podczas pobierania pliku vCard.', 'error');
            }
        }
        
        function shareContact(name, email, phone, company) {
            if (navigator.share) {
                navigator.share({
                    title: `Kontakt: ${name}`,
                    text: `${name}\n${company}\nEmail: ${email}${phone ? `\nTelefon: ${phone}` : ''}`,
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback - copy to clipboard
                const contactText = `${name}\n${company}\nEmail: ${email}${phone ? `\nTelefon: ${phone}` : ''}`;
                navigator.clipboard.writeText(contactText).then(() => {
                    showNotification('Dane kontaktu zostały skopiowane do schowka!', 'success');
                }).catch(() => {
                    showNotification('Nie udało się skopiować danych kontaktu.', 'error');
                });
            }
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm animate-slide-up ${
                type === 'success' ? 'bg-success-100 text-success-800 border border-success-200' :
                type === 'error' ? 'bg-danger-100 text-danger-800 border border-danger-200' :
                type === 'warning' ? 'bg-warning-100 text-warning-800 border border-warning-200' :
                'bg-primary-100 text-primary-800 border border-primary-200'
            }`;
            
            const icon = type === 'success' ? 
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' :
                type === 'error' ?
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' :
                '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            
            notification.innerHTML = `
                <div class="flex items-center">
                    ${icon}
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Close modal when clicking outside or pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeContactModal();
            }
        });
        
        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactModal();
            }
        });
    </script>
</body>
</html>