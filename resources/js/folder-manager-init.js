/**
 * Folder Manager Initialization Script
 * This script initializes the Alpine.js component with data from PHP
 */
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Alpine.js to be ready
    document.addEventListener('alpine:init', () => {
        // Component is registered in folder-manager.js
        console.log('Folder Manager Alpine.js component initialized');
    });
});

// Helper function to initialize component with PHP data
window.initializeFolderManager = function(config) {
    if (window.folderManagerInstance) {
        window.folderManagerInstance.viewMode = config.viewMode || 'grid';
        window.folderManagerInstance.totalItems = config.totalItems || 0;
    }
};
