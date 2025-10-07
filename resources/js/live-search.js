/**
 * Live Search with Debouncing
 * Provides React-like search experience with dropdown results
 */

class LiveSearch {
    constructor() {
        this.searchInput = document.querySelector('.navbar-search .search-input');
        this.dropdown = document.getElementById('search-dropdown');
        this.resultsList = document.getElementById('search-results-list');
        this.viewAllLink = document.getElementById('view-all-results');
        this.debounceTimer = null;
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        if (!this.searchInput) return;
        
        // Event listeners
        this.searchInput.addEventListener('input', (e) => {
            this.handleInput(e);
        });
        
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.length >= 2) {
                this.showDropdown();
            }
        });
        
        this.searchInput.addEventListener('blur', (e) => {
            // Delay hiding to allow clicking on results
            setTimeout(() => {
                if (!this.dropdown.contains(document.activeElement)) {
                    this.hideDropdown();
                }
            }, 200);
        });
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar-search-container')) {
                this.hideDropdown();
            }
        });
        
        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
    }
    
    handleInput(e) {
        const query = e.target.value.trim();
        
        // Clear previous timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        // Debounce the search
        this.debounceTimer = setTimeout(() => {
            if (query.length >= 2) {
                this.performSearch(query);
            } else {
                this.hideDropdown();
            }
        }, 300); // 300ms debounce
    }
    
    async performSearch(query) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const response = await fetch(`/wp-content/themes/heygirlsbkk/ajax-search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.displayResults(data.results, query);
            
            // Update view all link
            this.viewAllLink.href = `/?s=${encodeURIComponent(query)}`;
            
        } catch (error) {
            console.error('Search error:', error);
            this.showError();
        } finally {
            this.isLoading = false;
        }
    }
    
    displayResults(results, query) {
        if (results.length === 0) {
            this.resultsList.innerHTML = `
                <div class="search-no-results">
                    <p>No results found for "${query}"</p>
                </div>
            `;
        } else {
            this.resultsList.innerHTML = results.map(result => `
                <div class="search-result-item ${result.is_product ? 'search-result-product' : ''}" data-url="${result.url}">
                    ${result.thumbnail ? `
                        <div class="search-result-image">
                            <img src="${result.thumbnail}" alt="${result.title}" loading="lazy" />
                        </div>
                    ` : ''}
                    <div class="search-result-content">
                        <div class="search-result-title">${this.highlightQuery(result.title, query)}</div>
                     
                    </div>
                </div>
            `).join('');
            
            // Add click handlers to results
            this.resultsList.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', () => {
                    window.location.href = item.dataset.url;
                });
            });
        }
        
        this.showDropdown();
    }
    
    highlightQuery(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    showLoading() {
        this.resultsList.innerHTML = `
            <div class="search-loading">
                <div class="search-spinner"></div>
                <p>Searching...</p>
            </div>
        `;
        this.showDropdown();
    }
    
    showError() {
        this.resultsList.innerHTML = `
            <div class="search-error">
                <p>Search temporarily unavailable</p>
            </div>
        `;
        this.showDropdown();
    }
    
    showDropdown() {
        this.dropdown.style.display = 'block';
    }
    
    hideDropdown() {
        this.dropdown.style.display = 'none';
    }
    
    handleKeydown(e) {
        const results = this.resultsList.querySelectorAll('.search-result-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            // Focus first result
            if (results.length > 0) {
                results[0].focus();
            }
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new LiveSearch();
});
