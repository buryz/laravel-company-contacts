/**
 * Real-time search functionality for contacts
 */

// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search suggestions functionality
class SearchSuggestions {
    constructor(inputElement, suggestionsContainer) {
        this.input = inputElement;
        this.container = suggestionsContainer;
        this.suggestions = [];
        this.selectedIndex = -1;
        
        this.init();
    }
    
    init() {
        this.input.addEventListener('input', debounce((e) => {
            this.handleInput(e.target.value);
        }, 300));
        
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target) && e.target !== this.input) {
                this.hideSuggestions();
            }
        });
    }
    
    async handleInput(query) {
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }
        
        try {
            const response = await fetch(`/search/suggestions?query=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.suggestions = data.suggestions;
                this.showSuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }
    
    showSuggestions() {
        if (this.suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        const html = this.suggestions.map((suggestion, index) => `
            <div class="suggestion-item px-3 py-2 cursor-pointer hover:bg-gray-100 ${index === this.selectedIndex ? 'bg-gray-100' : ''}" 
                 data-index="${index}" data-value="${suggestion.value}">
                ${suggestion.label}
            </div>
        `).join('');
        
        this.container.innerHTML = html;
        this.container.classList.remove('hidden');
        
        // Add click listeners
        this.container.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectSuggestion(item.dataset.value);
            });
        });
    }
    
    hideSuggestions() {
        this.container.classList.add('hidden');
        this.selectedIndex = -1;
    }
    
    handleKeydown(e) {
        if (!this.container.classList.contains('hidden')) {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    this.updateSelection();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.updateSelection();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0) {
                        this.selectSuggestion(this.suggestions[this.selectedIndex].value);
                    }
                    break;
                case 'Escape':
                    this.hideSuggestions();
                    break;
            }
        }
    }
    
    updateSelection() {
        this.container.querySelectorAll('.suggestion-item').forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('bg-gray-100');
            } else {
                item.classList.remove('bg-gray-100');
            }
        });
    }
    
    selectSuggestion(value) {
        this.input.value = value;
        this.hideSuggestions();
        
        // Trigger search
        if (window.Alpine && window.Alpine.store) {
            // If using Alpine.js store
            const searchStore = window.Alpine.store('search');
            if (searchStore) {
                searchStore.performSearch();
            }
        } else {
            // Trigger custom event
            this.input.dispatchEvent(new CustomEvent('suggestion-selected', {
                detail: { value }
            }));
        }
    }
}

// Group management functionality
class GroupManager {
    constructor() {
        this.expandedGroups = {
            company: {},
            position: {}
        };
    }
    
    toggleGroup(type, groupName) {
        if (!this.expandedGroups[type]) {
            this.expandedGroups[type] = {};
        }
        this.expandedGroups[type][groupName] = !this.expandedGroups[type][groupName];
        
        // Trigger custom event for UI updates
        document.dispatchEvent(new CustomEvent('group-toggled', {
            detail: { type, groupName, expanded: this.expandedGroups[type][groupName] }
        }));
    }
    
    isGroupExpanded(type, groupName) {
        return this.expandedGroups[type] && this.expandedGroups[type][groupName];
    }
    
    expandAllGroups(type) {
        const groups = document.querySelectorAll(`[data-group-type="${type}"]`);
        groups.forEach(group => {
            const groupName = group.dataset.groupName;
            this.expandedGroups[type][groupName] = true;
        });
    }
    
    collapseAllGroups(type) {
        if (this.expandedGroups[type]) {
            Object.keys(this.expandedGroups[type]).forEach(groupName => {
                this.expandedGroups[type][groupName] = false;
            });
        }
    }
    
    clearExpandedGroups() {
        this.expandedGroups = {
            company: {},
            position: {}
        };
    }
}

// Contact search with grouping functionality
class ContactSearchWithGroups {
    constructor() {
        this.groupManager = new GroupManager();
        this.currentViewMode = 'list';
        this.searchData = {
            isSearching: false,
            contacts: [],
            groups: [],
            total: 0
        };
        this.tagSearchMode = 'any'; // 'any' or 'all'
    }
    
    async performGroupedSearch(query, filters, groupType) {
        const endpoint = groupType === 'company' ? '/search/group-by-company' : '/search/group-by-position';
        
        const params = new URLSearchParams();
        if (query && query.trim()) {
            params.append('query', query.trim());
        }
        if (filters.company) {
            params.append('company', filters.company);
        }
        if (filters.position) {
            params.append('position', filters.position);
        }
        if (filters.tags && filters.tags.length > 0) {
            filters.tags.forEach(tag => {
                params.append('tags[]', tag);
            });
        }
        
        try {
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
                this.searchData.groups = data.groups;
                this.searchData.total = data.total;
                this.searchData.isSearching = true;
                
                // Trigger custom event for UI updates
                document.dispatchEvent(new CustomEvent('grouped-search-completed', {
                    detail: { groups: data.groups, total: data.total, groupType }
                }));
                
                return data;
            } else {
                throw new Error(data.error || 'Search failed');
            }
        } catch (error) {
            console.error('Grouped search failed:', error);
            this.searchData.groups = [];
            this.searchData.total = 0;
            throw error;
        }
    }
    
    setViewMode(mode) {
        this.currentViewMode = mode;
        
        // Clear expanded groups when switching view modes
        if (mode === 'list') {
            this.groupManager.clearExpandedGroups();
        }
        
        // Trigger custom event
        document.dispatchEvent(new CustomEvent('view-mode-changed', {
            detail: { mode }
        }));
    }
    
    getGroupManager() {
        return this.groupManager;
    }

    /**
     * Perform search specifically by tags
     */
    async searchByTags(tagIds, searchMode = 'any') {
        if (!tagIds || tagIds.length === 0) {
            this.searchData.contacts = [];
            this.searchData.total = 0;
            this.searchData.isSearching = false;
            return;
        }

        const params = new URLSearchParams();
        tagIds.forEach(tagId => {
            params.append('tag_ids[]', tagId);
        });
        params.append('search_mode', searchMode);

        try {
            const response = await fetch(`/search/by-tags?${params.toString()}`, {
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
                this.searchData.contacts = data.contacts;
                this.searchData.total = data.total;
                this.searchData.isSearching = true;
                this.tagSearchMode = data.search_mode;

                // Trigger custom event for UI updates
                document.dispatchEvent(new CustomEvent('tag-search-completed', {
                    detail: { 
                        contacts: data.contacts, 
                        total: data.total, 
                        searchMode: data.search_mode 
                    }
                }));

                return data;
            } else {
                throw new Error(data.error || 'Tag search failed');
            }
        } catch (error) {
            console.error('Tag search failed:', error);
            this.searchData.contacts = [];
            this.searchData.total = 0;
            throw error;
        }
    }

    /**
     * Get available tags for filters
     */
    async getAvailableTags() {
        try {
            const response = await fetch('/search/available-tags', {
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
                return data.tags;
            } else {
                throw new Error(data.error || 'Failed to get available tags');
            }
        } catch (error) {
            console.error('Failed to get available tags:', error);
            return [];
        }
    }

    /**
     * Set tag search mode
     */
    setTagSearchMode(mode) {
        this.tagSearchMode = mode;
        
        // Trigger custom event
        document.dispatchEvent(new CustomEvent('tag-search-mode-changed', {
            detail: { mode }
        }));
    }

    /**
     * Get current tag search mode
     */
    getTagSearchMode() {
        return this.tagSearchMode;
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SearchSuggestions, debounce, GroupManager, ContactSearchWithGroups };
}