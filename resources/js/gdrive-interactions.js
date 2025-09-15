/**
 * Google Drive Style Interactive Features
 * For Filament Media Manager
 */

document.addEventListener('DOMContentLoaded', function() {

    // View Mode Toggle Functionality
    const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewMode = this.getAttribute('data-view');

            // Update active state
            viewToggleButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Toggle views
            if (viewMode === 'grid') {
                gridView?.classList.remove('hidden');
                listView?.classList.add('hidden');
            } else {
                gridView?.classList.add('hidden');
                listView?.classList.remove('hidden');
            }

            // Save preference
            fetch('/api/user-preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    folder_view_mode: viewMode
                })
            }).catch(console.error);
        });
    });

    // Search Functionality
    const searchInput = document.querySelector('input[type="search"]');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase();

            searchTimeout = setTimeout(() => {
                filterFolders(query);
            }, 300);
        });
    }

    function filterFolders(query) {
        const folderItems = document.querySelectorAll('.folder-grid-item, .folder-list-item');

        folderItems.forEach(item => {
            const folderName = item.querySelector('h3')?.textContent.toLowerCase() || '';
            const folderDesc = item.querySelector('p')?.textContent.toLowerCase() || '';

            if (folderName.includes(query) || folderDesc.includes(query)) {
                item.style.display = '';
                item.style.animation = 'fadeIn 0.3s ease-in-out';
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide empty state
        updateEmptyState(query);
    }

    function updateEmptyState(query) {
        const visibleItems = document.querySelectorAll('.folder-grid-item:not([style*="display: none"]), .folder-list-item:not([style*="display: none"])');
        const emptyStates = document.querySelectorAll('.text-center.py-12');

        emptyStates.forEach(emptyState => {
            if (visibleItems.length === 0 && query) {
                emptyState.innerHTML = `
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No folders found for "${query}"</h3>
                    <p class="text-gray-500 dark:text-gray-400">Try different search terms</p>
                `;
                emptyState.classList.remove('hidden');
            } else if (visibleItems.length > 0) {
                emptyState.classList.add('hidden');
            }
        });
    }

    // Select All Functionality
    const selectAllBtn = document.querySelector('button:contains("Select all")');
    let allSelected = false;

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            allSelected = !allSelected;

            checkboxes.forEach(checkbox => {
                checkbox.checked = allSelected;
                triggerSelectionChange(checkbox);
            });

            this.textContent = allSelected ? 'Deselect all' : 'Select all';
        });
    }

    // Individual checkbox handling
    document.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox') {
            triggerSelectionChange(e.target);
            updateSelectAllButton();
        }
    });

    function triggerSelectionChange(checkbox) {
        const card = checkbox.closest('.gdrive-folder-card, .folder-list-item');
        if (card) {
            if (checkbox.checked) {
                card.classList.add('selected');
                card.style.backgroundColor = 'rgba(26, 115, 232, 0.1)';
                card.style.borderColor = '#1a73e8';
            } else {
                card.classList.remove('selected');
                card.style.backgroundColor = '';
                card.style.borderColor = '';
            }
        }
    }

    function updateSelectAllButton() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
        const selectAllBtn = document.querySelector('button:contains("Select all")');

        if (selectAllBtn) {
            if (checkedBoxes.length === 0) {
                selectAllBtn.textContent = 'Select all';
                allSelected = false;
            } else if (checkedBoxes.length === checkboxes.length) {
                selectAllBtn.textContent = 'Deselect all';
                allSelected = true;
            } else {
                selectAllBtn.textContent = `${checkedBoxes.length} selected`;
            }
        }
    }

    // Sort Functionality
    const sortSelect = document.querySelector('select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            sortFolders(sortBy);
        });
    }

    function sortFolders(sortBy) {
        const container = document.querySelector('.folders-grid .grid, .folders-list > div');
        if (!container) return;

        const items = Array.from(container.children);

        items.sort((a, b) => {
            let aValue, bValue;

            switch (sortBy) {
                case 'name':
                    aValue = a.querySelector('h3')?.textContent.toLowerCase() || '';
                    bValue = b.querySelector('h3')?.textContent.toLowerCase() || '';
                    return aValue.localeCompare(bValue);

                case 'date':
                    aValue = a.querySelector('[data-date]')?.getAttribute('data-date') || 0;
                    bValue = b.querySelector('[data-date]')?.getAttribute('data-date') || 0;
                    return new Date(bValue) - new Date(aValue);

                case 'size':
                    aValue = parseInt(a.querySelector('[data-size]')?.getAttribute('data-size') || 0);
                    bValue = parseInt(b.querySelector('[data-size]')?.getAttribute('data-size') || 0);
                    return bValue - aValue;

                default:
                    return 0;
            }
        });

        // Re-append sorted items
        items.forEach(item => container.appendChild(item));

        // Add animation
        items.forEach((item, index) => {
            item.style.animation = `fadeIn 0.3s ease-in-out ${index * 0.05}s both`;
        });
    }

    // Keyboard Navigation
    document.addEventListener('keydown', function(e) {
        const focusedElement = document.activeElement;

        // Arrow key navigation in grid
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            if (focusedElement.classList.contains('folder-grid-item') || focusedElement.classList.contains('folder-list-item')) {
                e.preventDefault();
                navigateGrid(e.key, focusedElement);
            }
        }

        // Enter to open folder
        if (e.key === 'Enter' && (focusedElement.classList.contains('folder-grid-item') || focusedElement.classList.contains('folder-list-item'))) {
            e.preventDefault();
            focusedElement.click();
        }

        // Space to select
        if (e.key === ' ' && (focusedElement.classList.contains('folder-grid-item') || focusedElement.classList.contains('folder-list-item'))) {
            e.preventDefault();
            const checkbox = focusedElement.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                triggerSelectionChange(checkbox);
            }
        }
    });

    function navigateGrid(direction, currentElement) {
        const items = Array.from(document.querySelectorAll('.folder-grid-item, .folder-list-item'));
        const currentIndex = items.indexOf(currentElement);
        let nextIndex;

        switch (direction) {
            case 'ArrowRight':
                nextIndex = Math.min(currentIndex + 1, items.length - 1);
                break;
            case 'ArrowLeft':
                nextIndex = Math.max(currentIndex - 1, 0);
                break;
            case 'ArrowDown':
                // Calculate based on grid columns
                const gridCols = getComputedStyle(document.querySelector('.folders-grid .grid')).gridTemplateColumns.split(' ').length;
                nextIndex = Math.min(currentIndex + gridCols, items.length - 1);
                break;
            case 'ArrowUp':
                const gridColsUp = getComputedStyle(document.querySelector('.folders-grid .grid')).gridTemplateColumns.split(' ').length;
                nextIndex = Math.max(currentIndex - gridColsUp, 0);
                break;
        }

        if (nextIndex !== undefined && items[nextIndex]) {
            items[nextIndex].focus();
        }
    }

    // Performance optimizations
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            // Recalculate grid layouts if needed
            console.log('Window resized, recalculating layouts');
        }, 250);
    });

    // Initialize tooltips for truncated text
    function initTooltips() {
        const truncatedElements = document.querySelectorAll('.truncate, .line-clamp-2');
        truncatedElements.forEach(element => {
            if (element.scrollWidth > element.clientWidth || element.scrollHeight > element.clientHeight) {
                element.title = element.textContent;
            }
        });
    }

    // Initialize on load
    initTooltips();

    // Add loading states
    function showLoading(container) {
        container.innerHTML = `
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                ${Array(12).fill().map(() => `
                    <div class="folder-skeleton rounded-xl h-40 bg-gray-200 dark:bg-gray-700"></div>
                `).join('')}
            </div>
        `;
    }

    // Intersection Observer for lazy loading
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.gdrive-folder-card').forEach(card => {
            observer.observe(card);
        });
    }
});

// Helper function for text selection
function containsText(selector, text) {
    return Array.from(document.querySelectorAll(selector)).find(el =>
        el.textContent.includes(text)
    );
}
