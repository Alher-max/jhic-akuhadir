# Task Plan: Fix Kelas/Rombel Dropdown Display Issue

## Problem Analysis
- User created 2 classes: "Kelas 1A" and "Kelas 1B" (both SD Tingkat 1)
- Dropdown shows identical options: `(SD - Tingkat 1)` twice
- Class names ("Kelas 1A", "Kelas 1B") are NOT displayed

## Root Cause
The `SchoolClass` model uses column `nama_kelas` but the view references `$class->full_name` which doesn't exist as an accessor.

## Steps to Fix
- [x] Analyze SchoolClass model structure (columns: nama_kelas, jenjang, tingkat)
- [x] Analyze modal.blade.php view - uses $class->full_name which doesn't exist
- [x] Add `full_name` accessor to SchoolClass model returning nama_kelas
- [x] Update edit modal dropdown to include tingkat for consistency
- [x] Verify all dropdowns (Tambah, Edit, Import, Filter) use full_name accessor
- [x] Verify controller fetches all necessary columns (nama_kelas, jenjang, tingkat)

## Files Modified
1. `app/Models/SchoolClass.php` - Added `getFullNameAttribute()` accessor
2. `resources/views/students/partials/modal.blade.php` - Updated edit modal dropdown format

## Verification
The dropdown will now display:
- "Kelas 1A (SD - Tingkat 1)"
- "Kelas 1B (SD - Tingkat 1)"

Instead of:
- "(SD - Tingkat 1)"
- "(SD - Tingkat 1)"