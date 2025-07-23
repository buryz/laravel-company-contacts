<x-contacts-layout>
    <x-slot name="title">Lista kontaktów</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Książka Kontaktów Firmowych</h1>
            @auth
                <div class="flex space-x-3">
                    <a href="{{ route('tags.index') }}" 
                       class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        Tagi
                    </a>
                    <a href="{{ route('contacts.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Dodaj kontakt
                    </a>
                    <button x-show="searchData.contacts.length > 0 || (!searchData.isSearching && {{ $contacts->count() }} > 0)"
                            @click="exportContacts()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Eksportuj CSV
                    </button>
                </div>
            @endauth
        </div>
    </x-slot>

    <div x-data="contactSearch()" class="space-y-6">
        <!-- Real-time Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Real-time Search -->
                    <div class="relative">
                        <label for="realtime-search" class="block text-sm font-medium text-gray-700 mb-1">Wyszukaj w czasie rzeczywistym</label>
                        <div class="relative">
                            <input type="text" 
                                   id="realtime-search"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="performSearch()"
                                   placeholder="Imię, nazwisko, firma, stanowisko..."
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!isLoading" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <svg x-show="isLoading" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div>
                        <label for="company-filter" class="block text-sm font-medium text-gray-700 mb-1">Firma</label>
                        <select id="company-filter" 
                                x-model="filters.company"
                                @change="performSearch()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Wszystkie firmy</option>
                            @foreach($companies as $company)
                                <option value="{{ $company }}">{{ $company }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Position Filter -->
                    <div>
                        <label for="position-filter" class="block text-sm font-medium text-gray-700 mb-1">Stanowisko</label>
                        <select id="position-filter"
                                x-model="filters.position"
                                @change="performSearch()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Wszystkie stanowiska</option>
                            @foreach($positions as $position)
                                <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tags Filter -->
                    <div>
                        <label for="tags-filter" class="block text-sm font-medium text-gray-700 mb-1">Tagi</label>
                        <select id="tags-filter"
                                x-model="filters.tags"
                                @change="performSearch()"
                                multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <!-- Tag Search Mode Toggle -->
                        <div x-show="filters.tags.length > 0" class="mt-2">
                            <div class="flex bg-gray-100 rounded-md p-1 text-xs">
                                <button @click="setTagSearchMode('any')"
                                        :class="tagSearchMode === 'any' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                        class="px-2 py-1 rounded-md transition-colors flex-1">
                                    Dowolny tag
                                </button>
                                <button @click="setTagSearchMode('all')"
                                        :class="tagSearchMode === 'all' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-600 hover:text-gray-800'"
                                        class="px-2 py-1 rounded-md transition-colors flex-1">
                                    Wszystkie tagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div class="flex space-x-3">
                        <button @click="clearFilters()" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            Wyczyść filtry
                        </button>
                        
                        <!-- View Mode Toggle -->
                        <div class="flex bg-gray-100 rounded-md p-1">
                            <button @click="setViewMode('list')"
                                    :class="viewMode === 'list' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Lista
                            </button>
                            <button @click="setViewMode('company')"
                                    :class="viewMode === 'company' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Firmy
                            </button>
                            <button @click="setViewMode('position')"
                                    :class="viewMode === 'position' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6M8 8v6a2 2 0 002 2h4a2 2 0 002-2V8M8 8V6a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                </svg>
                                Stanowiska
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">
                        Znaleziono: <span class="font-medium" x-text="getDisplayTotal()"></span> kontaktów
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts List/Groups -->
        <div x-show="hasContacts()" class="bg-white rounded-lg shadow-sm overflow-hidden">
            
            <!-- List View -->
            <div x-show="viewMode === 'list'" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kontakt
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Firma / Stanowisko
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tagi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Akcje
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Real-time search results -->
                        <template x-if="searchData.isSearching">
                            <template x-for="contact in searchData.contacts" :key="contact.id">
                                <tr class="hover:bg-gray-50" x-data="contactRow(contact)">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600" x-text="contact.initials"></span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="contact.full_name"></div>
                                                <div class="text-sm text-gray-500">
                                                    <a :href="'mailto:' + contact.email" class="hover:text-blue-600" x-text="contact.email"></a>
                                                </div>
                                                <div x-show="contact.phone" class="text-sm text-gray-500">
                                                    <a :href="'tel:' + contact.phone" class="hover:text-blue-600" x-text="contact.phone"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900" x-text="contact.company"></div>
                                        <div class="text-sm text-gray-500" x-text="contact.position"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="tag in contact.tags" :key="tag.id">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :style="'background-color: ' + tag.color + '20; color: ' + tag.color + ';'"
                                                      x-text="tag.name">
                                                </span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="openContactModal(contact.id)"
                                                    class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Wizytówka
                                            </button>
                                            @auth
                                                <a :href="'/contacts/' + contact.id + '/edit'"
                                                   class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edytuj
                                                </a>
                                                <button @click="deleteContact(contact.id, contact.full_name)"
                                                        class="text-red-600 hover:text-red-900 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Usuń
                                                </button>
                                            @endauth
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <!-- Default server-side results -->
                        <template x-if="!searchData.isSearching">
                            @foreach($contacts as $contact)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600">
                                                        {{ strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $contact->full_name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    <a href="mailto:{{ $contact->email }}" class="hover:text-blue-600">{{ $contact->email }}</a>
                                                </div>
                                                @if($contact->phone)
                                                    <div class="text-sm text-gray-500">
                                                        <a href="tel:{{ $contact->phone }}" class="hover:text-blue-600">{{ $contact->phone }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $contact->company }}</div>
                                        <div class="text-sm text-gray-500">{{ $contact->position }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($contact->tags as $tag)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="openContactModal({{ $contact->id }})"
                                                    class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Wizytówka
                                            </button>
                                            @auth
                                                <a href="{{ route('contacts.edit', $contact) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                    Edytuj
                                                </a>
                                                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" 
                                                      class="inline"
                                                      onsubmit="return confirm('Czy na pewno chcesz usunąć kontakt {{ $contact->full_name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900 inline-flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        Usuń
                                                    </button>
                                                </form>
                                            @endauth
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Company Groups View -->
            <div x-show="viewMode === 'company'" class="divide-y divide-gray-200">
                <template x-for="group in groupedData.groups" :key="group.company">
                    <div class="group-container">
                        <div class="bg-gray-50 px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors"
                             @click="toggleGroup('company', group.company)">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                         :class="isGroupExpanded('company', group.company) ? 'rotate-90' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    <h3 class="ml-3 text-lg font-medium text-gray-900" x-text="group.company"></h3>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                      x-text="group.count + ' kontaktów'"></span>
                            </div>
                        </div>
                        <div x-show="isGroupExpanded('company', group.company)" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 max-h-0"
                             x-transition:enter-end="opacity-100 max-h-screen"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 max-h-screen"
                             x-transition:leave-end="opacity-0 max-h-0"
                             class="overflow-hidden">
                            <div class="bg-white">
                                <template x-for="contact in group.contacts" :key="contact.id">
                                    <div class="px-6 py-4 border-b border-gray-100 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-blue-600" x-text="contact.initials"></span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900" x-text="contact.full_name"></div>
                                                    <div class="text-sm text-gray-500" x-text="contact.position"></div>
                                                    <div class="text-sm text-gray-500">
                                                        <a :href="'mailto:' + contact.email" class="hover:text-blue-600" x-text="contact.email"></a>
                                                    </div>
                                                    <div x-show="contact.phone" class="text-sm text-gray-500">
                                                        <a :href="'tel:' + contact.phone" class="hover:text-blue-600" x-text="contact.phone"></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="tag in contact.tags" :key="tag.id">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                              :style="'background-color: ' + tag.color + '20; color: ' + tag.color + ';'"
                                                              x-text="tag.name">
                                                        </span>
                                                    </template>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button @click="openContactModal(contact.id)"
                                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                                        Wizytówka
                                                    </button>
                                                    @auth
                                                        <a :href="'/contacts/' + contact.id + '/edit'"
                                                           class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                            Edytuj
                                                        </a>
                                                        <button @click="deleteContact(contact.id, contact.full_name)"
                                                                class="text-red-600 hover:text-red-900 text-sm">
                                                            Usuń
                                                        </button>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Position Groups View -->
            <div x-show="viewMode === 'position'" class="divide-y divide-gray-200">
                <template x-for="group in groupedData.groups" :key="group.position">
                    <div class="group-container">
                        <div class="bg-gray-50 px-6 py-4 cursor-pointer hover:bg-gray-100 transition-colors"
                             @click="toggleGroup('position', group.position)">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                         :class="isGroupExpanded('position', group.position) ? 'rotate-90' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                    <h3 class="ml-3 text-lg font-medium text-gray-900" x-text="group.position"></h3>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                      x-text="group.count + ' kontaktów'"></span>
                            </div>
                        </div>
                        <div x-show="isGroupExpanded('position', group.position)" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 max-h-0"
                             x-transition:enter-end="opacity-100 max-h-screen"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 max-h-screen"
                             x-transition:leave-end="opacity-0 max-h-0"
                             class="overflow-hidden">
                            <div class="bg-white">
                                <template x-for="contact in group.contacts" :key="contact.id">
                                    <div class="px-6 py-4 border-b border-gray-100 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-blue-600" x-text="contact.initials"></span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900" x-text="contact.full_name"></div>
                                                    <div class="text-sm text-gray-500" x-text="contact.company"></div>
                                                    <div class="text-sm text-gray-500">
                                                        <a :href="'mailto:' + contact.email" class="hover:text-blue-600" x-text="contact.email"></a>
                                                    </div>
                                                    <div x-show="contact.phone" class="text-sm text-gray-500">
                                                        <a :href="'tel:' + contact.phone" class="hover:text-blue-600" x-text="contact.phone"></a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="tag in contact.tags" :key="tag.id">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                              :style="'background-color: ' + tag.color + '20; color: ' + tag.color + ';'"
                                                              x-text="tag.name">
                                                        </span>
                                                    </template>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button @click="openContactModal(contact.id)"
                                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                                        Wizytówka
                                                    </button>
                                                    @auth
                                                        <a :href="'/contacts/' + contact.id + '/edit'"
                                                           class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                            Edytuj
                                                        </a>
                                                        <button @click="deleteContact(contact.id, contact.full_name)"
                                                                class="text-red-600 hover:text-red-900 text-sm">
                                                            Usuń
                                                        </button>
                                                    @endauth
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pagination (only for list view and non-search results) -->
            <template x-if="viewMode === 'list' && !searchData.isSearching">
                @if($contacts->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $contacts->appends(request()->query())->links() }}
                    </div>
                @endif
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!hasContacts()" 
             class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.196M17 20H7m10 0v-2c0-5.523-3.477-10-10-10s-10 4.477-10 10v2m10 0H7m0 0H2v-2a3 3 0 015.196-2.196M7 20v-2m5-10a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Brak kontaktów</h3>
            <p class="mt-1 text-sm text-gray-500">
                <span x-show="searchData.isSearching">Nie znaleziono kontaktów spełniających kryteria wyszukiwania.</span>
                <span x-show="!searchData.isSearching">
                    @if(array_filter($filters ?? []))
                        Nie znaleziono kontaktów spełniających kryteria wyszukiwania.
                    @else
                        Rozpocznij od dodania pierwszego kontaktu.
                    @endif
                </span>
            </p>
            @auth
                <div x-show="!searchData.isSearching && !{{ array_filter($filters ?? []) ? 'true' : 'false' }}" class="mt-6">
                    <a href="{{ route('contacts.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Dodaj pierwszy kontakt
                    </a>
                </div>
            @endauth
        </div>

        <!-- Contact Modal -->
        <div x-data="contactModal()" 
             x-show="isOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="closeModal()">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="isOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="closeModal()"></div>

                <!-- Modal panel -->
                <div x-show="isOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    
                    <!-- Loading state -->
                    <div x-show="isLoading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Ładowanie wizytówki...</p>
                    </div>

                    <!-- Error state -->
                    <div x-show="error && !isLoading" class="text-center py-8">
                        <svg class="h-12 w-12 text-red-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-red-600" x-text="error"></p>
                        <button @click="closeModal()" 
                                class="mt-4 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm">
                            Zamknij
                        </button>
                    </div>

                    <!-- Contact card content -->
                    <div x-show="contact && !isLoading && !error">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <!-- Header -->
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-lg font-medium text-blue-600" 
                                                      x-text="contact ? contact.first_name.charAt(0) + contact.last_name.charAt(0) : ''"></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-medium text-gray-900" x-text="contact ? contact.full_name : ''"></h3>
                                            <p class="text-sm text-gray-500" x-text="contact ? contact.position : ''"></p>
                                            <p class="text-sm text-gray-500" x-text="contact ? contact.company : ''"></p>
                                        </div>
                                    </div>
                                    <button @click="closeModal()" 
                                            class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Contact details -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <a :href="'mailto:' + (contact ? contact.email : '')" 
                                           class="text-blue-600 hover:text-blue-800" 
                                           x-text="contact ? contact.email : ''"></a>
                                    </div>
                                    <div x-show="contact && contact.phone" class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <a :href="'tel:' + (contact ? contact.phone : '')" 
                                           class="text-blue-600 hover:text-blue-800" 
                                           x-text="contact ? contact.phone : ''"></a>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div x-show="contact && contact.tags && contact.tags.length > 0" class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Tagi:</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="tag in (contact ? contact.tags : [])" :key="tag.id">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :style="'background-color: ' + tag.color + '20; color: ' + tag.color + ';'"
                                                  x-text="tag.name">
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- QR Code -->
                                <div x-show="qrCode" class="text-center mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Kod QR - Wizytówka:</h4>
                                    <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                                        <img :src="qrCode" alt="QR Code" class="w-32 h-32 mx-auto">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Zeskanuj kodem telefonu, aby dodać kontakt</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end space-x-3">
                                    <button @click="downloadVCard()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Pobierz vCard
                                    </button>
                                    <button @click="closeModal()" 
                                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                        Zamknij
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function contactSearch() {
            return {
                searchQuery: '',
                isLoading: false,
                viewMode: 'list', // 'list', 'company', 'position'
                expandedGroups: {
                    company: {},
                    position: {}
                },
                filters: {
                    company: '',
                    position: '',
                    tags: []
                },
                tagSearchMode: 'any', // 'any' or 'all'
                searchData: {
                    isSearching: false,
                    contacts: [],
                    total: 0
                },
                groupedData: {
                    groups: [],
                    total: 0
                },

                async performSearch() {
                    // If no search query and no filters, show default results
                    if (!this.searchQuery.trim() && !this.filters.company && !this.filters.position && this.filters.tags.length === 0) {
                        this.searchData.isSearching = false;
                        this.groupedData.groups = [];
                        return;
                    }

                    this.isLoading = true;
                    this.searchData.isSearching = true;

                    try {
                        let endpoint = '/search';
                        if (this.viewMode === 'company') {
                            endpoint = '/search/group-by-company';
                        } else if (this.viewMode === 'position') {
                            endpoint = '/search/group-by-position';
                        }

                        const params = new URLSearchParams();
                        if (this.searchQuery.trim()) {
                            params.append('query', this.searchQuery.trim());
                        }
                        if (this.filters.company) {
                            params.append('company', this.filters.company);
                        }
                        if (this.filters.position) {
                            params.append('position', this.filters.position);
                        }
                        this.filters.tags.forEach(tag => {
                            params.append('tags[]', tag);
                        });
                        if (this.filters.tags.length > 0) {
                            params.append('tag_search_mode', this.tagSearchMode);
                        }

                        const response = await fetch(`${endpoint}?${params.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        const data = await response.json();
                        
                        if (data.success) {
                            if (this.viewMode === 'list') {
                                this.searchData.contacts = data.contacts;
                                this.searchData.total = data.total;
                                this.groupedData.groups = [];
                            } else {
                                this.groupedData.groups = data.groups;
                                this.groupedData.total = data.total;
                                this.searchData.contacts = [];
                            }
                        } else {
                            console.error('Search error:', data.error);
                            this.searchData.contacts = [];
                            this.searchData.total = 0;
                            this.groupedData.groups = [];
                        }
                    } catch (error) {
                        console.error('Search failed:', error);
                        this.searchData.contacts = [];
                        this.searchData.total = 0;
                        this.groupedData.groups = [];
                    } finally {
                        this.isLoading = false;
                    }
                },

                setViewMode(mode) {
                    this.viewMode = mode;
                    // Re-perform search if we have active search/filters
                    if (this.searchData.isSearching) {
                        this.performSearch();
                    }
                },

                toggleGroup(type, groupName) {
                    if (!this.expandedGroups[type]) {
                        this.expandedGroups[type] = {};
                    }
                    this.expandedGroups[type][groupName] = !this.expandedGroups[type][groupName];
                },

                isGroupExpanded(type, groupName) {
                    return this.expandedGroups[type] && this.expandedGroups[type][groupName];
                },

                hasContacts() {
                    if (this.viewMode === 'list') {
                        return (this.searchData.isSearching && this.searchData.contacts.length > 0) || 
                               (!this.searchData.isSearching && {{ $contacts->count() }} > 0);
                    } else {
                        return this.groupedData.groups.length > 0 || 
                               (!this.searchData.isSearching && {{ $contacts->count() }} > 0);
                    }
                },

                getDisplayTotal() {
                    if (this.viewMode === 'list') {
                        return this.searchData.isSearching ? this.searchData.total : {{ $contacts->total() }};
                    } else {
                        return this.groupedData.total || (this.searchData.isSearching ? 0 : {{ $contacts->total() }});
                    }
                },

                clearFilters() {
                    this.searchQuery = '';
                    this.filters.company = '';
                    this.filters.position = '';
                    this.filters.tags = [];
                    this.tagSearchMode = 'any';
                    this.searchData.isSearching = false;
                    this.searchData.contacts = [];
                    this.searchData.total = 0;
                    this.groupedData.groups = [];
                    this.groupedData.total = 0;
                    this.expandedGroups = {
                        company: {},
                        position: {}
                    };
                },

                setTagSearchMode(mode) {
                    this.tagSearchMode = mode;
                    // Re-perform search if we have active tag filters
                    if (this.filters.tags.length > 0) {
                        this.performSearch();
                    }
                },

                exportContacts() {
                    const params = new URLSearchParams();
                    if (this.searchQuery.trim()) {
                        params.append('search', this.searchQuery.trim());
                    }
                    if (this.filters.company) {
                        params.append('company', this.filters.company);
                    }
                    if (this.filters.position) {
                        params.append('position', this.filters.position);
                    }
                    this.filters.tags.forEach(tag => {
                        params.append('tags[]', tag);
                    });

                    window.location.href = `/contacts/export?${params.toString()}`;
                },

                deleteContact(contactId, contactName) {
                    if (confirm(`Czy na pewno chcesz usunąć kontakt ${contactName}?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/contacts/${contactId}`;
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';
                        
                        form.appendChild(csrfInput);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            }
        }

        function contactRow(contact) {
            return {
                contact: contact
            }
        }

        function contactModal() {
            return {
                isOpen: false,
                isLoading: false,
                contact: null,
                qrCode: null,
                vcard: null,
                error: null,

                openModal(contactId) {
                    this.isOpen = true;
                    this.isLoading = true;
                    this.error = null;
                    this.contact = null;
                    this.qrCode = null;
                    this.vcard = null;

                    this.fetchContactData(contactId);
                },

                async fetchContactData(contactId) {
                    try {
                        const response = await fetch(`/contacts/${contactId}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Nie udało się pobrać danych kontaktu');
                        }

                        const data = await response.json();
                        
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        this.contact = data.contact;
                        this.qrCode = data.qr_code;
                        this.vcard = data.vcard;
                    } catch (error) {
                        console.error('Error fetching contact data:', error);
                        this.error = error.message || 'Wystąpił błąd podczas pobierania danych kontaktu';
                    } finally {
                        this.isLoading = false;
                    }
                },

                closeModal() {
                    this.isOpen = false;
                    this.contact = null;
                    this.qrCode = null;
                    this.vcard = null;
                    this.error = null;
                },

                downloadVCard() {
                    if (!this.vcard || !this.contact) return;

                    const blob = new Blob([this.vcard], { type: 'text/vcard' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${this.contact.full_name}.vcf`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            }
        }

        // Global function to open contact modal (for server-side rendered buttons)
        function openContactModal(contactId) {
            // Find the modal component and open it
            const modalElement = document.querySelector('[x-data*="contactModal()"]');
            if (modalElement && modalElement._x_dataStack) {
                const modalData = modalElement._x_dataStack[0];
                if (modalData && modalData.openModal) {
                    modalData.openModal(contactId);
                }
            }
        }
    </script>
</x-contacts-layout>