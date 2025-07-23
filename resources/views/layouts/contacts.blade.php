<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Książka Kontaktów Firmowych' }} - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and main navigation -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="{{ route('contacts.index') }}" class="flex items-center">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="ml-2 text-xl font-semibold text-gray-900">Książka Kontaktów</span>
                            </a>
                        </div>
                        
                        <!-- Main navigation links -->
                        <div class="hidden md:ml-10 md:flex md:space-x-8">
                            <a href="{{ route('contacts.index') }}" 
                               class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('contacts.index') ? 'border-blue-500 text-blue-600' : '' }}">
                                Wszystkie kontakty
                            </a>
                            @auth
                                <a href="{{ route('contacts.create') }}" 
                                   class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('contacts.create') ? 'border-blue-500 text-blue-600' : '' }}">
                                    Dodaj kontakt
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Authentication section -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">
                                    Witaj, <span class="font-medium">{{ auth()->user()->name }}</span>
                                </span>
                                <form method="POST" action="{{ route('auth.logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                                        Wyloguj się
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('auth.login') }}" 
                                   class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                    Zaloguj się
                                </a>
                                <a href="{{ route('auth.register') }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                                    Zarejestruj się
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden" x-data="{ open: false }">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3" x-show="open" x-transition>
                    <a href="{{ route('contacts.index') }}" 
                       class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">
                        Wszystkie kontakty
                    </a>
                    @auth
                        <a href="{{ route('contacts.create') }}" 
                           class="text-gray-600 hover:text-gray-900 block px-3 py-2 rounded-md text-base font-medium">
                            Dodaj kontakt
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        @if (isset($header))
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    <!-- Contact Modal for QR Code Display -->
    <div id="contactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Wizytówka kontaktu</h3>
                    <button onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function openContactModal(contactId) {
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
                    alert('Wystąpił błąd podczas pobierania danych kontaktu.');
                    return;
                }
                
                const contact = data.contact;
                const vcard = data.vcard;
                
                document.getElementById('modalTitle').textContent = `${contact.first_name} ${contact.last_name}`;
                
                let tagsHtml = '';
                if (contact.tags && contact.tags.length > 0) {
                    tagsHtml = '<div class="mb-4"><h4 class="text-sm font-medium text-gray-700 mb-2">Tagi:</h4><div class="flex flex-wrap gap-2">';
                    contact.tags.forEach(tag => {
                        tagsHtml += `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: ${tag.color}20; color: ${tag.color};">${tag.name}</span>`;
                    });
                    tagsHtml += '</div></div>';
                }
                
                document.getElementById('modalContent').innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Informacje kontaktowe:</h4>
                                <div class="space-y-2 text-sm">
                                    <p><span class="font-medium">Imię i nazwisko:</span> ${contact.first_name} ${contact.last_name}</p>
                                    <p><span class="font-medium">Email:</span> <a href="mailto:${contact.email}" class="text-blue-600 hover:text-blue-800">${contact.email}</a></p>
                                    ${contact.phone ? `<p><span class="font-medium">Telefon:</span> <a href="tel:${contact.phone}" class="text-blue-600 hover:text-blue-800">${contact.phone}</a></p>` : ''}
                                    <p><span class="font-medium">Firma:</span> ${contact.company}</p>
                                    <p><span class="font-medium">Stanowisko:</span> ${contact.position}</p>
                                </div>
                                ${tagsHtml}
                            </div>
                            <div class="text-center">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Kod QR:</h4>
                                <div id="qrcode" class="flex justify-center"></div>
                                <p class="text-xs text-gray-500 mt-2">Zeskanuj kod QR, aby dodać kontakt do telefonu</p>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button onclick="downloadVCard('${contact.first_name}_${contact.last_name}', \`${vcard.replace(/`/g, '\\`')}\`)" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Pobierz vCard
                            </button>
                            <button onclick="closeContactModal()" 
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                                Zamknij
                            </button>
                        </div>
                    </div>
                `;
                
                // Generate QR code
                generateQRCode(vcard);
                
                document.getElementById('contactModal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas pobierania danych kontaktu.');
            });
        }
        
        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }
        
        function generateQRCode(vcard) {
            // Simple QR code generation using a service
            const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(vcard)}`;
            document.getElementById('qrcode').innerHTML = `<img src="${qrCodeUrl}" alt="QR Code" class="mx-auto">`;
        }
        
        function downloadVCard(name, vcard) {
            const blob = new Blob([vcard], { type: 'text/vcard' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${name}.vcf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
        
        // Close modal when clicking outside
        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactModal();
            }
        });
    </script>
</body>
</html>