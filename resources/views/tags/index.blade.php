<x-contacts-layout>
    <x-slot name="title">Zarządzanie tagami</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Zarządzanie tagami</h1>
            <div class="flex space-x-3">
                <a href="{{ route('contacts.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Powrót do kontaktów
                </a>
                <a href="{{ route('tags.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Dodaj tag
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if($tags->count() > 0)
            <!-- Tags Grid -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Lista tagów ({{ $tags->count() }})</h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @foreach($tags as $tag)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center space-x-4">
                                <!-- Tag Color and Name -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-4 h-4 rounded-full border border-gray-300" 
                                         style="background-color: {{ $tag->color }};"></div>
                                    <span class="font-medium text-gray-900">{{ $tag->name }}</span>
                                </div>
                                
                                <!-- Tag Preview -->
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                    {{ $tag->name }}
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <!-- Contact Count -->
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">{{ $tag->contacts_count }}</span>
                                    {{ $tag->contacts_count === 1 ? 'kontakt' : 'kontaktów' }}
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('tags.show', $tag) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Pokaż
                                    </a>
                                    <a href="{{ route('tags.edit', $tag) }}" 
                                       class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        Edytuj
                                    </a>
                                    <form method="POST" 
                                          action="{{ route('tags.destroy', $tag) }}" 
                                          class="inline"
                                          onsubmit="return confirm('Czy na pewno chcesz usunąć tag \'{{ $tag->name }}\'? {{ $tag->contacts_count > 0 ? 'Tag zostanie usunięty z ' . $tag->contacts_count . ' kontaktów.' : '' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Usuń
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Brak tagów</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Nie masz jeszcze żadnych tagów. Utwórz pierwszy tag, aby rozpocząć kategoryzację kontaktów.
                </p>
                <div class="mt-6">
                    <a href="{{ route('tags.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Dodaj pierwszy tag
                    </a>
                </div>
            </div>
        @endif

        <!-- Help Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">O tagach</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Tagi pomagają w kategoryzacji i organizacji kontaktów</li>
                            <li>Każdy tag może mieć własny kolor dla łatwiejszego rozpoznawania</li>
                            <li>Kontakty mogą mieć przypisanych wiele tagów jednocześnie</li>
                            <li>Usunięcie tagu spowoduje jego usunięcie ze wszystkich kontaktów</li>
                            <li>Możesz wyszukiwać kontakty według przypisanych tagów</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-contacts-layout>