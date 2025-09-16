/**
 * Alpine.js Folder Manager Component
 * Manages folder selection, actions, and view modes
 */
function folderManager() {
    return {
        // State
        selectedItems: new Set(),
        viewMode: 'grid', // Will be set from PHP
        totalItems: 0,
        selectAll: false,
        folderSelected: false,
        searchQuery: '',

        // Initialize
        init() {
            console.log('Folder Manager initialized with viewMode:', this.viewMode);
            this.updateTotalItems();
        },

        // Selection Methods
        toggleFolder(folderId) {
            if (this.selectedItems.has(folderId)) {
                this.selectedItems.delete(folderId);
            } else {
                this.selectedItems.add(folderId);
            }
            this.updateSelectAllState();
        },

        toggleSelectAll() {
            if (this.selectAll) {
                // Select all folders
                document.querySelectorAll('[data-folder-id]').forEach(element => {
                    const folderId = element.getAttribute('data-folder-id');
                    this.selectedItems.add(folderId);
                });
            } else {
                // Deselect all folders
                this.selectedItems.clear();
            }
        },

        updateSelectAllState() {
            const totalFolders = document.querySelectorAll('[data-folder-id]').length;
            this.selectAll = this.selectedItems.size === totalFolders;
        },

        clearSelection() {
            this.selectedItems.clear();
            this.selectAll = false;
        },

        updateTotalItems() {
            this.totalItems = document.querySelectorAll('[data-folder-id]').length;
        },

        // View Mode Methods
        setViewMode(mode) {
            this.viewMode = mode;
            this.clearSelection(); // Clear selection when switching views
        },

        // Action Methods
        bulkDelete() {
            if (this.selectedItems.size === 0) return;

            if (confirm(`Are you sure you want to delete ${this.selectedItems.size} folder(s)?`)) {
                console.log('Bulk delete:', Array.from(this.selectedItems));
                // TODO: Implement actual delete logic
                // Example: Call Laravel route with selected IDs
                this.performBulkAction('delete', Array.from(this.selectedItems));
            }
        },

        bulkEdit() {
            if (this.selectedItems.size === 0) return;

            console.log('Bulk edit:', Array.from(this.selectedItems));
            // TODO: Implement bulk edit modal/form
            this.performBulkAction('edit', Array.from(this.selectedItems));
        },

        bulkMove() {
            if (this.selectedItems.size === 0) return;

            console.log('Bulk move:', Array.from(this.selectedItems));
            // TODO: Implement move to folder selection
            this.performBulkAction('move', Array.from(this.selectedItems));
        },

        // Individual Action Methods
        editFolder(folderId) {
            console.log('Edit folder:', folderId);
            // TODO: Implement edit logic
            // Example: Open edit modal or navigate to edit page
        },

        deleteFolder(folderId) {
            if (confirm('Are you sure you want to delete this folder?')) {
                console.log('Delete folder:', folderId);
                // TODO: Implement delete logic
                this.performAction('delete', folderId);
            }
        },

        moveFolder(folderId) {
            console.log('Move folder:', folderId);
            // TODO: Implement move logic
            this.performAction('move', folderId);
        },

        duplicateFolder(folderId) {
            console.log('Duplicate folder:', folderId);
            // TODO: Implement duplicate logic
            this.performAction('duplicate', folderId);
        },

        // Helper Methods
        performBulkAction(action, folderIds) {
            // TODO: Implement actual API calls
            console.log(`Performing bulk ${action} on:`, folderIds);

            // Example implementation:
            // fetch('/api/folders/bulk-action', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     },
            //     body: JSON.stringify({
            //         action: action,
            //         folder_ids: folderIds
            //     })
            // }).then(response => {
            //     if (response.ok) {
            //         this.clearSelection();
            //         location.reload(); // Or update UI dynamically
            //     }
            // });
        },

        performAction(action, folderId) {
            // TODO: Implement actual API calls
            console.log(`Performing ${action} on folder:`, folderId);

            // Example implementation:
            // fetch(`/api/folders/${folderId}/${action}`, {
            //     method: action === 'delete' ? 'DELETE' : 'POST',
            //     headers: {
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     }
            // }).then(response => {
            //     if (response.ok) {
            //         location.reload(); // Or update UI dynamically
            //     }
            // });
        },

        handleFolderClick(event) {
            // Prevent navigation if clicking on controls
            if (event.target.closest('.folder-selection') ||
                event.target.closest('.folder-actions-menu') ||
                event.target.closest('.list-actions-menu') ||
                event.target.closest('.folder-checkbox')) {
                event.preventDefault();
                return false;
            }
            // Allow normal navigation
            return true;
        },

        // Search Methods
        filterFolders() {
            const query = this.searchQuery.toLowerCase();
            document.querySelectorAll('[data-folder-name]').forEach(item => {
                const name = item.getAttribute('data-folder-name');
                if (name.includes(query)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        },

        // Watchers
        $watch: {
            searchQuery() {
                this.filterFolders();
            }
        }
    }
}

// Make function available globally for Alpine.js
window.folderManager = folderManager;
