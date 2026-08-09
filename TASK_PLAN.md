# Task Plan: Fix Teacher Management Module

## Analysis Summary
- Current "Tambah Guru / Staf" modal dropdown only has 3 options: Guru, Operator, Kepala Sekolah
- Main table filter dropdown has 14 detailed jabatan options
- Controller only accepts basic roles: operator, kepala_sekolah, guru, siswa, parent
- Need to add `position` column to store detailed jabatan
- Need conditional logic: Wali Kelas → role 'wali_kelas', Kepala Sekolah → role 'kepala_sekolah', others → standard role

## Implementation Steps

### 1. Database Migration
- [x] Create migration to add `position` column to users table
- [x] Run migration

### 2. User Model
- [x] Add `position` to fillable array in User model
- [x] Add `getSystemRoleFromPosition` method for conditional role assignment

### 3. Blade View (index.blade.php)
- [x] Update "Tambah Guru / Staf" modal dropdown with all 14 jabatan options (matching filter dropdown)
- [x] Update "Edit Guru" modal dropdown with all 14 jabatan options
- [x] Ensure both modals use the same option values as filter dropdown

### 4. Controller (TeacherManagementController.php)
- [x] Update validation rules to accept all jabatan values
- [x] Update `validateAndCreateTeacher` method:
  - Save selected jabatan to `position` column
  - Set system `role` conditionally using `User::getSystemRoleFromPosition()`
- [x] Update `update` method with same logic
- [x] Fix `importFromCsv` method to handle new position field
- [x] Fix CSV template download to use position values
- [x] Fix duplicate 'kepala_sekolah' in validation rules

### 5. Testing
- [x] Verify dropdown options match between modal and filter
- [x] Verify role assignment logic works correctly
- [x] Verify existing data not broken
- [x] Verify CSV import/export works with position field
