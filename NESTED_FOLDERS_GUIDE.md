# Nested Folders Implementation Guide

## Overview

The Filament Media Manager now supports **unlimited nested folder hierarchies**, allowing you to organize your media files in a tree-like structure with folders within folders.

## Database Structure

### Migration

A new migration has been added: `0001__01_01_555555_add_parent_id_to_folders_table.stub`

```sql
ALTER TABLE folders ADD COLUMN parent_id BIGINT UNSIGNED NULL AFTER id;
ALTER TABLE folders ADD CONSTRAINT folders_parent_id_foreign 
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE;
```

### Model Relationships

The `Folder` model includes:

```php
// Get all subfolders
public function folders()
{
    return $this->hasMany(Folder::class, 'parent_id');
}

// Get parent folder
public function parent()
{
    return $this->belongsTo(Folder::class, 'parent_id');
}
```

## Features Implemented

### 1. **Hierarchical Query Filtering**

Folders are filtered by `parent_id` in the table query:

```php
// Show only root folders (no parent)
$query->whereNull('parent_id');

// Show subfolders of specific parent
$query->where('parent_id', $parentId);
```

### 2. **Breadcrumb Navigation**

Dynamic breadcrumbs show the full folder path:

```
Dashboard > Folder A > Folder B > Folder C
```

Implementation in `ListFolders::getBreadcrumbs()`:
- Recursively builds parent trail
- Each breadcrumb is clickable to navigate

### 3. **Parent Folder Selector**

When creating a folder, you can select its parent:

```php
Forms\Components\Select::make('parent_id')
    ->label('Parent Folder')
    ->relationship('parent', 'name')
    ->searchable()
    ->preload()
```

### 4. **Smart Navigation**

When clicking a folder:
- If folder has subfolders → Navigate into folder
- If folder has no subfolders → Show media files

```php
$hasSubfolders = Folder::where('parent_id', $folderId)->exists();

if ($hasSubfolders) {
    return redirect(FolderResource::getUrl('index', ['parent_id' => $folderId]));
}
```

### 5. **Back Navigation Button**

Header action to go back to parent folder:

```php
Actions\Action::make('back_to_parent')
    ->label('Back to Parent')
    ->icon('heroicon-o-arrow-left')
    ->url(/* parent URL */);
```

### 6. **Visual Indicators**

Folders show:
- **Badge** with subfolder count
- **Parent context** (shows "in Parent Folder Name")

### 7. **Context Inheritance**

Subfolders inherit properties from parent:
- `model_type`
- `model_id`
- User access settings

## How to Enable

### Step 1: Enable in Plugin

```php
// app/Providers/Filament/AdminPanelProvider.php
->plugin(
    \Juniyasyos\FilamentMediaManager\FilamentMediaManagerPlugin::make()
        ->allowSubFolders()
)
```

### Step 2: Run Migration

```bash
php artisan filament-media-manager:install
```

Or manually:
```bash
php artisan migrate
```

## Usage Examples

### Creating Root Folder

1. Go to Media Manager
2. Click "Create"
3. Leave "Parent Folder" empty or select "Root Folder"
4. Fill in folder details

### Creating Subfolder - Method 1

1. Navigate into a parent folder
2. Click "Create" in header
3. New folder will automatically have parent set
4. Fill in folder details

### Creating Subfolder - Method 2

1. Click "Create" from any view
2. Select parent from "Parent Folder" dropdown
3. Fill in folder details

### Navigating Nested Folders

1. Click on a folder to enter it
2. View subfolders or media
3. Use breadcrumbs to jump to any level
4. Use "Back to Parent" button to go up one level

## API Structure

When enabled, nested folder structure is available via:

```
GET /api/folders
{
  "data": [
    {
      "id": 1,
      "name": "Parent Folder",
      "parent_id": null,
      "folders": [
        {
          "id": 2,
          "name": "Child Folder",
          "parent_id": 1,
          "folders": [...]
        }
      ]
    }
  ]
}
```

## Technical Details

### Query Scopes

Root folders query:
```php
$query->whereNull('parent_id')
    ->where('model_id', null)
    ->where('collection', null);
```

Subfolder query:
```php
$query->where('parent_id', $parentId);
```

### Cascade Delete

When a parent folder is deleted:
- All subfolders are automatically deleted (CASCADE)
- All media in subfolders are handled by Spatie Media Library

### Performance Considerations

- Folder tree is loaded on-demand (not eager loaded)
- Breadcrumb trail uses recursive query (cached in memory)
- Subfolder count uses efficient `exists()` check

## Translation Keys

### English
```php
'parent_folder' => 'Parent Folder',
'back' => 'Back to Parent',
'sub-folder-created' => 'Subfolder Created',
```

### Indonesian
```php
'parent_folder' => 'Folder Induk',
'back' => 'Kembali ke Induk',
'sub-folder-created' => 'Subfolder Dibuat',
```

## Troubleshooting

### Folders not showing subfolders

Check:
1. `allowSubFolders()` is enabled in plugin
2. Migration has run successfully
3. `parent_id` column exists in database

### Breadcrumbs not working

Check:
1. `parent_id` relationships are set correctly
2. No circular references (folder as its own parent)

### Can't navigate back

Check:
1. Request parameter `parent_id` is present
2. Parent folder exists and is not deleted

## Future Enhancements

Potential improvements:
- Drag & drop folder reorganization
- Folder tree sidebar widget
- Max nesting level configuration
- Folder templates
- Bulk move folders
- Folder permissions inheritance

## Testing

To test nested folders:

```php
// Create parent
$parent = Folder::create(['name' => 'Parent']);

// Create child
$child = Folder::create([
    'name' => 'Child',
    'parent_id' => $parent->id
]);

// Check relationship
$parent->folders; // Returns collection with $child
$child->parent; // Returns $parent
```

## Conclusion

The nested folder implementation provides a robust, user-friendly way to organize media files hierarchically. It integrates seamlessly with existing features like user access control, folder protection, and model associations.
