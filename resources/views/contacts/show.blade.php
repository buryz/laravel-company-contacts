<x-contacts-layout>
    <x-slot name="title">{{ $contact->full_name }}</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">{{ $contact->full_name }}</h1>
            <div class="flex space-x-3">
                @auth
                    <a href="{{ route('contacts.edit', $contact) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edytuj
                    </a>
                @endauth
                <a href="{{ route('contacts.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Powrót do listy
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Contact Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-xl font-medium text-blue-600">
                                    {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h2 class="text-2xl font-bold text-gray-900">{{ $contact->full_name }}</h2>
                            <p class="text-lg text-gray-600">{{ $contact->position }}</p>
                            <p class="text-md text-gray-500">{{ $contact->company }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contact Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informacje kontaktowe</h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1">
                                        <a href="mailto:{{ $contact->email }}" 
                                           class="text-blue-600 hover:text-blue-800 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $contact->email }}
                                        </a>
                                    </dd>
                                </div>

                                @if($contact->phone)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                                        <dd class="mt-1">
                                            <a href="tel:{{ $contact->phone }}" 
                                               class="text-blue-600 hover:text-blue-800 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                {{ $contact->phone }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Firma</dt>
                                    <dd class="mt-1 text-gray-900">{{ $contact->company }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stanowisko</dt>
                                    <dd class="mt-1 text-gray-900">{{ $contact->position }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Tags -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Tagi</h3>
                            @if($contact->tags->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($contact->tags as $tag)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Brak przypisanych tagów</p>
                            @endif
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informacje systemowe</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500">Utworzony</dt>
                                <dd class="mt-1 text-gray-900">{{ $contact->created_at->format('d.m.Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Ostatnia modyfikacja</dt>
                                <dd class="mt-1 text-gray-900">{{ $contact->updated_at->format('d.m.Y H:i') }}</dd>
                            </div>
                            @if($contact->creator)
                                <div>
                                    <dt class="font-medium text-gray-500">Utworzony przez</dt>
                                    <dd class="mt-1 text-gray-900">{{ $contact->creator->name }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- QR Code Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Kod QR</h3>
                    <div class="text-center">
                        <div id="qrcode" class="mb-4"></div>
                        <p class="text-sm text-gray-500 mb-4">
                            Zeskanuj kod QR, aby dodać kontakt do telefonu
                        </p>
                        <button onclick="downloadVCard('{{ $contact->first_name }}_{{ $contact->last_name }}', `{{ $contact->toVCard() }}`)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Pobierz vCard
                        </button>
                    </div>
                </div>

                <!-- Actions Card -->
                @auth
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Akcje</h3>
                        <div class="space-y-3">
                            <a href="{{ route('contacts.edit', $contact) }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edytuj kontakt
                            </a>
                            
                            <form method="POST" action="{{ route('contacts.destroy', $contact) }}" 
                                  onsubmit="return confirm('Czy na pewno chcesz usunąć kontakt {{ $contact->full_name }}? Ta operacja jest nieodwracalna.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Usuń kontakt
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <script>
        // Generate QR code on page load
        document.addEventListener('DOMContentLoaded', function() {
            const vcard = `{{ $contact->toVCard() }}`;
            generateQRCode(vcard);
        });
        
        function generateQRCode(vcard) {
            const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(vcard)}`;
            document.getElementById('qrcode').innerHTML = `<img src="${qrCodeUrl}" alt="QR Code" class="mx-auto rounded">`;
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
    </script>
</x-contacts-layout>