# Task Progress: Self-Service & Webhook Integrasi Mesin Biometrik

## Backend Migration & Models
- [x] Create migration `biometric_devices` (id, school_id, serial_number, device_name, status ['pending','active','disabled'], ip_address, last_ping_at)
- [x] Create migration `biometric_user_mappings` (school_id, biometric_pin, student_id, teacher_id)
- [x] Create migration `biometric_raw_logs` (serial_number, raw_payload, status, error_message)
- [x] Create migration `add_biometric_secret_key_to_attendance_settings_table`
- [x] Run all migrations
- [x] Create model `BiometricDevice`
- [x] Create model `BiometricUserMapping`
- [x] Create model `BiometricRawLog`
- [x] Update `AttendanceSetting` model to include `biometric_secret_key` in fillable

## Endpoint API Receiver
- [x] Create `BiometricPushController` for HTTP POST/GET from machines
- [x] Implement Auto-Discovery: create record with status 'pending' if SN not in DB
- [x] Update `last_ping_at` and `ip_address` on every request
- [x] Register route `/api/v1/biometric/push` in routes/api.php

## Frontend UI (`dashboard/attendance-settings?tab=alat`)
- [ ] Add "Instruksi Setup Mandiri (Self-Service)" section with:
  - [ ] Endpoint URL (read-only + copy button)
  - [ ] Secret Key (read-only + copy button)
- [ ] Update "Perangkat Terdaftar" table:
  - [ ] Add tab/notification badge "Mesin Baru Ditemukan (Pending Claim)"
  - [ ] Add "Klaim & Hubungkan" button for pending devices
  - [ ] Add status indicator: 'Online' (green if ping < 5 min) and 'Offline' (gray)
- [ ] Add JavaScript for copy-to-clipboard functionality
- [ ] Add JavaScript for claim device functionality

## Testing & Verification
- [ ] Test API endpoint with sample payload
- [ ] Test frontend UI functionality
- [ ] Verify auto-discovery works correctly
- [ ] Verify claim functionality works