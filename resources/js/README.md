# Filament Media Manager JavaScript Files

This directory contains the JavaScript files for the Filament Media Manager package.

## Files Structure

### Core Components
- **folder-manager.js** - Main Alpine.js component for folder management functionality
- **folder-manager-init.js** - Initialization helper for binding PHP data to Alpine.js

## Usage

### 1. Publishing Assets
After installing the package, publish the JavaScript assets:

```bash
php artisan vendor:publish --tag=filament-media-manager-assets
```

This will copy the JavaScript files to `public/vendor/filament-media-manager/js/`

### 2. Including in Blade Templates

The JavaScript files are automatically included in the folder management views using Laravel's `@push('scripts')` directive:

```php
@push('scripts')
<script src="{{ asset('vendor/filament-media-manager/js/folder-manager.js') }}"></script>
<script src="{{ asset('vendor/filament-media-manager/js/folder-manager-init.js') }}"></script>
<script>
    // Initialize component with PHP data
    document.addEventListener('alpine:init', () => {
        window.initializeFolderManager({
            viewMode: '{{ $viewMode }}',
            totalItems: {{ count($records) }}
        });
    });
</script>
@endpush
```

## Components

### folderManager() - Alpine.js Component

The main Alpine.js component that provides:

#### State Management
- `selectedItems` - Set of selected folder IDs
- `viewMode` - Current view mode ('grid' or 'list')
- `totalItems` - Total number of items
- `selectAll` - Select all checkbox state
- `searchQuery` - Current search query

#### Methods
- **Selection Methods**
  - `toggleFolder(folderId)` - Toggle folder selection
  - `toggleSelectAll()` - Toggle all folders selection
  - `clearSelection()` - Clear all selections

- **View Methods**
  - `setViewMode(mode)` - Switch between grid/list view

- **Action Methods**
  - `bulkDelete()` - Delete selected folders
  - `bulkEdit()` - Edit selected folders
  - `bulkMove()` - Move selected folders
  - `editFolder(folderId)` - Edit single folder
  - `deleteFolder(folderId)` - Delete single folder
  - `moveFolder(folderId)` - Move single folder
  - `duplicateFolder(folderId)` - Duplicate single folder

- **Search Methods**
  - `filterFolders()` - Filter folders by search query

#### Usage in Blade Templates

```html
<div x-data="folderManager()" x-init="init()">
    <!-- Selection toolbar -->
    <div x-show="selectedItems.size > 0" x-transition>
        <span x-text="selectedItems.size"></span> selected
        <button @click="bulkDelete()">Delete</button>
    </div>
    
    <!-- Folder grid/list -->
    <div class="folder-item" 
         :class="{ 'selected': selectedItems.has('folder-id') }"
         @click="toggleFolder('folder-id')">
        <!-- Folder content -->
    </div>
</div>
```

## Development

### Adding New Features

1. **Add new state variables** in `folder-manager.js`:
```javascript
return {
    // Existing state...
    newFeature: false,
    // ...
}
```

2. **Add new methods** in the component:
```javascript
newFeatureMethod() {
    // Implementation
},
```

3. **Use in Blade templates** with Alpine.js directives:
```html
<div x-show="newFeature" @click="newFeatureMethod()">
    <!-- Content -->
</div>
```

### Customization

You can customize the component behavior by:

1. Modifying the JavaScript files after publishing
2. Extending the component with additional methods
3. Overriding the initialization script

## Dependencies

- **Alpine.js** - Required for reactive functionality
- **Laravel** - For asset publishing and Blade integration
- **Filament** - Parent framework integration

## Browser Support

Compatible with all modern browsers that support:
- ES6+ JavaScript features
- Alpine.js framework
- CSS Grid and Flexbox
