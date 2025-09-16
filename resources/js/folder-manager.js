/**
 * Optimized Alpine.js Folder Manager Component
 * Clean separation of concerns from blade template
 */
function folderManager() {
    return {
        selectedItems: new Set(),
        viewMode: 'grid',
        totalItems: 0,
        selectAll: false,

        init() {
            // Set viewMode immediately from data attribute
            const el = this.$el;
            this.viewMode = el.dataset.viewMode || 'grid';
            console.log('Folder Manager initialized with viewMode:', this.viewMode);

            this.updateTotalItems();
            this.setupWatchers();
        },

        setupWatchers() {
            this.$watch('selectedItems', () => {
                this.updateBodyClass();
                this.updateSelectAllState();
            });
        },

        updateBodyClass() {
            document.body.classList.toggle('selection-active', this.selectedItems.size > 0);
        },

        toggleFolder(folderId) {
            if (this.selectedItems.has(folderId)) {
                this.selectedItems.delete(folderId);
            } else {
                this.selectedItems.add(folderId);
            }
            this.selectedItems = new Set(this.selectedItems);
        },

        toggleSelectAll() {
            const allFolders = document.querySelectorAll('[data-folder-id]');
            const totalFolders = allFolders.length;

            if (this.selectedItems.size === totalFolders && totalFolders > 0) {
                this.selectedItems.clear();
            } else {
                this.selectedItems.clear();
                allFolders.forEach(element => {
                    this.selectedItems.add(element.getAttribute('data-folder-id'));
                });
            }
            this.selectedItems = new Set(this.selectedItems);
        },

        updateSelectAllState() {
            const totalFolders = document.querySelectorAll('[data-folder-id]').length;
            this.selectAll = this.selectedItems.size === totalFolders && totalFolders > 0;
        },

        clearSelection() {
            this.selectedItems.clear();
            this.selectedItems = new Set();
            this.selectAll = false;
        },

        updateTotalItems() {
            this.totalItems = document.querySelectorAll('[data-folder-id]').length;
            console.log('Total items found:', this.totalItems);
        },

        setViewMode(mode) {
            this.viewMode = mode;
            this.clearSelection();
        },

        bulkDelete() {
            if (this.selectedItems.size === 0) return;
            if (confirm(`Are you sure you want to delete ${this.selectedItems.size} folder(s)?`)) {
                this.performBulkAction('delete', Array.from(this.selectedItems));
            }
        },

        bulkEdit() {
            if (this.selectedItems.size === 0) return;
            this.performBulkAction('edit', Array.from(this.selectedItems));
        },

        bulkMove() {
            if (this.selectedItems.size === 0) return;
            this.performBulkAction('move', Array.from(this.selectedItems));
        },

        editFolder(folderId) {
            this.performAction('edit', folderId);
        },

        deleteFolder(folderId) {
            if (confirm('Are you sure you want to delete this folder?')) {
                this.performAction('delete', folderId);
            }
        },

        moveFolder(folderId) {
            this.performAction('move', folderId);
        },

        duplicateFolder(folderId) {
            this.performAction('duplicate', folderId);
        },

        performBulkAction(action, folderIds) {
            console.log(`Performing bulk ${action} on:`, folderIds);
        },

        performAction(action, folderId) {
            console.log(`Performing ${action} on folder:`, folderId);
        },

        handleFolderClick(event) {
            const isInteractive = event.target.closest('.folder-selection') ||
                                 event.target.closest('.folder-actions-menu') ||
                                 event.target.closest('.list-actions-menu') ||
                                 event.target.closest('.folder-checkbox');

            if (isInteractive) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    };
}

window.folderManager = folderManager;
