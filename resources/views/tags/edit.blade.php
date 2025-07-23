<x-contacts-layout>
    <x-slot name="title">Edytuj tag</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Edytuj tag: {{ $tag->name }}</h1>
            <a href="{{ route('tags.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Powrót do tagów
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto animate-fade-in">
        <div class="card-soft">
            <div class="card-body">
                <form method="POST" action="{{ route('tags.update', $tag) }}" class="space-y-8" x-data="tagForm()">
                    @csrf
                    @method('PUT')

                    <!-- Tag Name -->
                    <div class="space-y-2">
                        <label for="name" class="form-label">
                            <svg class="w-4 h-4 inline mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Nazwa tagu <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $tag->name) }}"
                               required
                               placeholder="np. VIP, Partner, Klient"
                               x-model="tagName"
                               class="form-input @error('name') form-input-error @enderror hover-glow focus-ring">
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="form-help">Wprowadź krótką i opisową nazwę dla tagu</p>
                    </div>

                    <!-- Tag Color -->
                    <div class="space-y-2">
                        <label for="color" class="form-label">
                            <svg class="w-4 h-4 inline mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM7 3H5a2 2 0 00-2 2v12a4 4 0 004 4h2a2 2 0 002-2V5a2 2 0 00-2-2z"></path>
                            </svg>
                            Kolor tagu <span class="text-danger-500">*</span>
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <input type="color" 
                                       id="color" 
                                       name="color" 
                                       value="{{ old('color', $tag->color) }}"
                                       required
                                       x-model="tagColor"
                                       class="h-12 w-20 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-primary-400 transition-colors duration-200 @error('color') border-danger-300 @enderror">
                                <div class="absolute -inset-1 bg-gradient-to-r from-primary-600 to-purple-600 rounded-lg opacity-0 hover:opacity-20 transition-opacity duration-200 pointer-events-none"></div>
                            </div>
                            <input type="text" 
                                   id="color_text" 
                                   x-model="tagColor"
                                   placeholder="#3B82F6"
                                   class="flex-1 form-input @error('color') form-input-error @enderror focus-ring">
                            <div class="flex space-x-2">
                                <button type="button" @click="setColor('#3B82F6')" class="w-8 h-8 bg-blue-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                                <button type="button" @click="setColor('#10B981')" class="w-8 h-8 bg-green-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                                <button type="button" @click="setColor('#F59E0B')" class="w-8 h-8 bg-yellow-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                                <button type="button" @click="setColor('#EF4444')" class="w-8 h-8 bg-red-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                                <button type="button" @click="setColor('#8B5CF6')" class="w-8 h-8 bg-purple-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                                <button type="button" @click="setColor('#06B6D4')" class="w-8 h-8 bg-cyan-500 rounded-full border-2 border-white shadow-sm hover:scale-110 transition-transform duration-200"></button>
                            </div>
                        </div>
                        @error('color')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        <p class="form-help">Wybierz kolor, który będzie reprezentował ten tag w interfejsie</p>
                    </div>

                    <!-- Tag Preview -->
                    <div class="space-y-2">
                        <label class="form-label">
                            <svg class="w-4 h-4 inline mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Podgląd tagu
                        </label>
                        <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-center space-x-4">
                                <span class="tag tag-lg hover-lift transition-all duration-300"
                                      :style="`background-color: ${tagColor}20; color: ${tagColor}; border: 1px solid ${tagColor}40;`"
                                      x-text="tagName || 'Przykładowy tag'">
                                </span>
                                <div class="text-sm text-gray-600">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 rounded-full" :style="`background-color: ${tagColor}`"></div>
                                        <span x-text="tagColor"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-8 border-t border-gray-200">
                        <a href="{{ route('tags.index') }}" 
                           class="btn-outline hover-lift">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Anuluj
                        </a>
                        <button type="submit" 
                                class="btn-primary hover-lift">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Zapisz zmiany
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tag Usage Info -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">Informacje o tagu</h3>
                    <div class="mt-2 text-sm text-gray-600">
                        <p><strong>Utworzony:</strong> {{ $tag->created_at->format('d.m.Y H:i') }}</p>
                        <p><strong>Ostatnia modyfikacja:</strong> {{ $tag->updated_at->format('d.m.Y H:i') }}</p>
                        @if($tag->creator)
                            <p><strong>Utworzony przez:</strong> {{ $tag->creator->name }}</p>
                        @endif
                        <p><strong>Liczba kontaktów:</strong> {{ $tag->contacts()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Uwaga</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Zmiany w nazwie i kolorze tagu będą widoczne we wszystkich kontaktach</li>
                            <li>Edycja tagu nie wpłynie na przypisania do kontaktów</li>
                            <li>Możesz anulować edycję w każdej chwili</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tagForm() {
            return {
                tagName: '{{ old('name', $tag->name) }}',
                tagColor: '{{ old('color', $tag->color) }}',
                
                setColor(color) {
                    this.tagColor = color;
                },
                
                init() {
                    // Watch for color changes and validate hex format
                    this.$watch('tagColor', (value) => {
                        if (!/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(value)) {
                            // If invalid hex, revert to previous valid color
                            if (value.length === 7 && !value.startsWith('#')) {
                                this.tagColor = '#' + value;
                            }
                        }
                    });
                }
            }
        }
    </script>
</x-contacts-layout>