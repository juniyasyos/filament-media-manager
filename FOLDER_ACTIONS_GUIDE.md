# Folder Actions - Quick Subfolder Creation

## Overview

Sekarang terdapat **action button** langsung di setiap folder card untuk membuat subfolder dengan cepat, tanpa perlu masuk ke dalam folder terlebih dahulu.

## Fitur yang Ditambahkan

### 1. **Action Button pada Folder Card** ✅

- Button muncul di pojok kanan atas folder card saat **hover**
- Icon: `heroicon-o-folder-plus` (folder dengan tanda plus)
- Warna: Success (hijau)
- Ukuran: Small (sm)

### 2. **Hover Effect** ✅

```css
opacity-0 group-hover:opacity-100 transition-opacity duration-200
```

Button tersembunyi dan hanya muncul saat hover untuk menjaga UI tetap clean.

### 3. **Form Modal** ✅

Ketika button diklik, akan muncul modal dengan form fields:
- Name (dengan auto-slug ke collection)
- Collection (readonly, auto-generated)
- Description
- Icon Picker
- Color Picker
- Is Protected (toggle)
- Password (conditional, muncul jika is_protected = true)
- Password Confirmation

### 4. **Auto-set Parent Context** ✅

- `parent_id` otomatis di-set ke folder yang diklik
- Inherit `model_type` dan `model_id` dari parent folder
- Inherit `user_id` dan `user_type` dari user yang login

## Cara Menggunakan

### **Metode 1: Quick Action Button (NEW!)**

1. Hover mouse ke folder card
2. Klik button **folder-plus** di pojok kanan atas
3. Isi form di modal yang muncul
4. Klik "Create"
5. Subfolder langsung terbuat di bawah folder tersebut

### **Metode 2: Navigate & Create**

1. Klik folder untuk masuk
2. Klik tombol "Create" di header
3. Folder baru otomatis jadi subfolder

### **Metode 3: Parent Selector**

1. Klik "Create" dari view manapun
2. Pilih parent dari dropdown "Parent Folder"
3. Isi detail folder

## Technical Implementation

### View (folder-action.blade.php)

```blade
<div class="relative group">
    {{-- Main folder action --}}
    <x-filament-actions::action ...>
        ...folder card content...
    </x-filament-actions::action>

    {{-- Quick action button - shown on hover --}}
    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-1">
        {{ ($this->createSubfolderAction($item))(['folder' => $item]) }}
    </div>
</div>
```

### Page Method (ListFolders.php)

```php
public function createSubfolderAction(?Folder $folder = null)
{
    return Actions\Action::make('createSubfolder')
        ->label(trans('filament-media-manager::messages.folders.actions.create_subfolder'))
        ->icon('heroicon-o-folder-plus')
        ->color('success')
        ->size('sm')
        ->visible(fn() => filament('filament-media-manager')->allowSubFolders)
        ->form([...])
        ->action(function (array $data, array $arguments) {
            $parentFolder = $arguments['folder'];
            
            $data['parent_id'] = $parentFolder->id;
            // ... inherit context ...
            
            Folder::create($data);
        });
}
```

## Translations

### English:
```php
'create_subfolder' => 'Create Subfolder',
'subfolder_created' => 'Subfolder created successfully',
```

### Indonesian:
```php
'create_subfolder' => 'Buat Subfolder',
'subfolder_created' => 'Subfolder berhasil dibuat',
```

## Visual Design

### Button Appearance:
- **Default state**: Hidden (opacity-0)
- **Hover state**: Visible dengan smooth transition
- **Position**: Absolute, top-2, right-2
- **Icon**: Folder dengan plus sign
- **Color**: Success/Green
- **Size**: Small untuk tidak mengganggu

### Positioning:
```
┌─────────────────────────┐
│ [+] ← Quick Action      │
│                         │
│    [Folder Icon]        │
│                         │
│    Folder Name          │
│    Created 2h ago       │
└─────────────────────────┘
```

## Benefits

✅ **Faster Workflow**: Create subfolder tanpa navigasi
✅ **Better UX**: Action context-aware langsung di folder
✅ **Clean UI**: Button hanya muncul saat hover
✅ **Consistent**: Menggunakan form yang sama dengan create biasa
✅ **Smart Inheritance**: Auto-inherit context dari parent

## Compatibility

- ✅ Works dengan `allowSubFolders()` setting
- ✅ Compatible dengan user access control
- ✅ Compatible dengan folder protection
- ✅ Works pada semua level nested folders
- ✅ Responsive design

## Future Enhancements

Potential additions:
- Edit folder button on hover
- Delete folder button on hover
- Move folder button on hover
- Copy folder button on hover
- Favorite toggle button on hover

## Example Use Cases

### Use Case 1: Project Organization
```
Projects/
├── 2024/
│   ├── Q1/ [Quick Action] → Create "January", "February", "March"
│   └── Q2/ [Quick Action] → Create "April", "May", "June"
```

### Use Case 2: Photo Albums
```
Photos/
├── Family/ [Quick Action] → Create "Wedding", "Birthday", "Vacation"
└── Work/ [Quick Action] → Create "Events", "Products", "Team"
```

### Use Case 3: Document Management
```
Documents/
├── Contracts/ [Quick Action] → Create "2024", "2023"
└── Invoices/ [Quick Action] → Create "Paid", "Pending"
```

## Conclusion

Fitur quick action button ini membuat workflow pembuatan nested folders menjadi jauh lebih efisien dan intuitive. User dapat langsung membuat subfolder dengan 2 klik tanpa perlu navigasi atau mencari parent folder di dropdown.
