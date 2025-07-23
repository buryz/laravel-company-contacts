<x-contacts-layout>
    <x-slot name="title">Tag: {{ $tag->name }}</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-bold text-gray-900">Tag:</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                      style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                    {{ $tag->name }}
                </span>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('tags.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Powrót do tagów
                </a>
                <a href="{{ route('tags.edit', $tag) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edytuj tag
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Tag Info -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informacje o tagu</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nazwa</dt>
                            <dd class="text-sm text-gray-900">{{ $tag->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kolor</dt>
                            <dd class="flex items-center space-x-2">
                                <div class="w-4 h-4 rounded-full border border-gray-300" 
                                     style="background-color: {{ $tag->color }};"></div>
                                <span class="text-sm text-gray-900">{{ $tag->color }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Liczba kontaktów</dt>
                            <dd class="text-sm text-gray-900">{{ $tag->contacts->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Utworzony</dt>
                            <dd class="text-sm text-gray-900">{{ $tag->created_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        @if($tag->creator)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Utworzony przez</dt>
                                <dd class="text-sm text-gray-900">{{ $tag->creator->name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Podgląd tagu</h3>
                    <div class="p-4 bg-gray-50 rounded-md">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                            {{ $tag->name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts with this tag -->
        @if($tag->contacts->count() > 0)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Kontakty z tym tagiem ({{ $tag->contacts->count() }})
                    </h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @foreach($tag->contacts as $contact)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">
                                        {{ $contact->full_name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $contact->position }} w {{ $contact->company }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $contact->email }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <!-- Contact Tags -->
                                @if($contact->tags->count() > 1)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($contact->tags as $contactTag)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                  style="background-color: {{ $contactTag->color }}20; color: {{ $contactTag->color }};">
                                                {{ $contactTag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <button onclick="showContactModal({{ $contact->id }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Pokaż
                                    </button>
                                    @auth
                                        <a href="{{ route('contacts.edit', $contact) }}" 
                                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            Edytuj
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- No contacts with this tag -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Brak kontaktów</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Ten tag nie jest jeszcze przypisany do żadnego kontaktu.
                </p>
                <div class="mt-6">
                    <a href="{{ route('contacts.index') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Przejdź do kontaktów
                    </a>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Akcje</h3>
            <div class="flex space-x-3">
                <a href="{{ route('tags.edit', $tag) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edytuj tag
                </a>
                
                <form method="POST" 
                      action="{{ route('tags.destroy', $tag) }}" 
                      class="inline"
                      onsubmit="return confirm('Czy na pewno chcesz usunąć tag \'{{ $tag->name }}\'? {{ $tag->contacts->count() > 0 ? 'Tag zostanie usunięty z ' . $tag->contacts->count() . ' kontaktów.' : '' }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Usuń tag
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Contact Modal (reuse from contacts.index) -->
    <div id="contactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
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
        function showContactModal(contactId) {
            const modal = document.getElementById('contactModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.innerHTML = '<div class="text-center py-4">Ładowanie...</div>';
            modal.classList.remove('hidden');
            
            fetch(`/contacts/${contactId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    modalContent.innerHTML = `<div class="text-red-600 text-center py-4">${data.error}</div>`;
                    return;
                }
                
                const contact = data.contact;
                const vcard = data.vcard;
                
                modalContent.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 mb-4">${contact.first_name} ${contact.last_name}</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">${contact.email}</dd>
                                </div>
                                ${contact.phone ? `
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Telefon</dt>
                                    <dd class="text-sm text-gray-900">${contact.phone}</dd>
                                </div>
                                ` : ''}
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Firma</dt>
                                    <dd class="text-sm text-gray-900">${contact.company}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stanowisko</dt>
                                    <dd class="text-sm text-gray-900">${contact.position}</dd>
                                </div>
                                ${contact.tags && contact.tags.length > 0 ? `
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tagi</dt>
                                    <dd class="flex flex-wrap gap-1 mt-1">
                                        ${contact.tags.map(tag => `
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                  style="background-color: ${tag.color}20; color: ${tag.color};">
                                                ${tag.name}
                                            </span>
                                        `).join('')}
                                    </dd>
                                </div>
                                ` : ''}
                            </dl>
                        </div>
                        <div class="text-center">
                            <h5 class="text-sm font-medium text-gray-700 mb-2">Kod QR - vCard</h5>
                            <div id="qrcode-${contact.id}" class="inline-block"></div>
                            <p class="text-xs text-gray-500 mt-2">Zeskanuj kodem telefonu aby dodać kontakt</p>
                        </div>
                    </div>
                `;
                
                // Generate QR code
                if (typeof QRCode !== 'undefined') {
                    new QRCode(document.getElementById(`qrcode-${contact.id}`), {
                        text: vcard,
                        width: 150,
                        height: 150
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = '<div class="text-red-600 text-center py-4">Wystąpił błąd podczas ładowania kontaktu.</div>';
            });
        }
        
        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactModal();
            }
        });
    </script>
</x-contacts-layout>