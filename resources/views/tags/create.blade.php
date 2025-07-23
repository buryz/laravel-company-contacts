<x-contacts-layout>
    <x-slot name="title">Dodaj tag</x-slot>
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Dodaj nowy tag</h1>
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
                <form method="POST" action="{{ route('tags.store') }}" class="space-y-8" x-data="tagForm()">
                    @csrf

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
                               value="{{ old('name') }}"
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
                            Kolor tagu
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <input type="color" 
                                       id="color" 
                                       name="color" 
                                       value="{{ old('color', '#3B82F6') }}"
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
                            Utwórz tag
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-8 card-soft animate-slide-up">
            <div class="card-body">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Wskazówki dotyczące tagów</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div class="space-y-3">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-success-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Nazwa tagu powinna być krótka i opisowa</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-success-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Wybierz kolor, który pomoże Ci szybko rozpoznać kategorię</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-success-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Możesz używać tagów do oznaczania typu klienta, priorytetu lub kategorii</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-success-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Przykłady tagów: VIP, Partner, Klient, Dostawca, Zespół</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Example Tags -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-sm font-medium text-gray-700 mb-2">Przykładowe tagi:</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="tag" style="background-color: #EF444420; color: #EF4444; border: 1px solid #EF444440;">VIP</span>
                                <span class="tag" style="background-color: #10B98120; color: #10B981; border: 1px solid #10B98140;">Partner</span>
                                <span class="tag" style="background-color: #3B82F620; color: #3B82F6; border: 1px solid #3B82F640;">Klient</span>
                                <span class="tag" style="background-color: #F59E0B20; color: #F59E0B; border: 1px solid #F59E0B40;">Dostawca</span>
                                <span class="tag" style="background-color: #8B5CF620; color: #8B5CF6; border: 1px solid #8B5CF640;">Zespół</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function tagForm() {
            return {
                tagName: '{{ old('name', '') }}',
                tagColor: '{{ old('color', '#3B82F6') }}',
                
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