# Panduan Setup Otomasi CI/CD HadirYuk (GitHub Actions)

Dokumen ini menjelaskan prosedur penyiapan akses SSH dan Secrets pada repositori GitHub agar pipeline CI/CD (`.github/workflows/deploy.yml`) dapat melakukan build, testing, dan deployment otomatis ke VPS produksi.

---

## 1. Pembuatan SSH Key Pair Khusus Deployment di VPS

Masuk ke server VPS via terminal, lalu buat key pair baru khusus untuk runner GitHub Actions:

```bash
# Generate SSH keypair menggunakan algoritma ed25519
ssh-keygen -t ed25519 -C "github-actions-deploy@hadiryuk" -f ~/.ssh/github_actions_deploy -N ""
```

Perintah di atas akan menghasilkan dua berkas di direktori `~/.ssh/`:
- `~/.ssh/github_actions_deploy` (Private Key — disalin ke GitHub Secret)
- `~/.ssh/github_actions_deploy.pub` (Public Key — didaftarkan di VPS)

---

## 2. Memasukkan Public Key ke `authorized_keys`

Tambahkan public key yang baru dibuat ke berkas otorisasi SSH pengguna di VPS:

```bash
cat ~/.ssh/github_actions_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

---

## 3. Konfigurasi GitHub Repository Secrets

Buka repositori HadirYuk di GitHub, lalu navigasikan ke:
**Settings** > **Secrets and variables** > **Actions** > Klik **New repository secret**.

Tambahkan 4 (empat) secrets berikut:

| Nama Secret | Deskripsi | Contoh Nilai |
| :--- | :--- | :--- |
| `SSH_HOST` | Alamat IP Publik atau domain VPS Anda | `103.xxx.xxx.xxx` |
| `SSH_USER` | Username login VPS tempat aplikasi berjalan | `thortech` atau `root` |
| `SSH_KEY` | **Seluruh isi** berkas Private Key (`~/.ssh/github_actions_deploy`) | `-----BEGIN OPENSSH PRIVATE KEY----- ...` |
| `SSH_PORT` | Port SSH server VPS (opsional, default: `22`) | `22` |

> [!TIP]
> Untuk melihat isi private key yang perlu disalin ke `SSH_KEY`:
> ```bash
> cat ~/.ssh/github_actions_deploy
> ```
> Pastikan menyalin seluruh teks mulai dari header `-----BEGIN OPENSSH PRIVATE KEY-----` sampai `-----END OPENSSH PRIVATE KEY-----`.

---

## 4. Konfigurasi Sudoers (Passwordless Restart Service)

Jika deployment dijalankan menggunakan user non-root (misal `thortech`), runner CI/CD memerlukan izin untuk merestart service systemd `hadiryuk` tanpa meminta input password interaktif.

Buat file konfigurasi sudoers baru di VPS:

```bash
sudo visudo -f /etc/sudoers.d/hadiryuk
```

Tambahkan baris berikut (sesuaikan `<username>` dengan user deploy Anda):

```sudoers
thortech ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart hadiryuk
```

Simpan berkas tersebut. Pastikan izin akses file adalah `0440`:

```bash
sudo chmod 0440 /etc/sudoers.d/hadiryuk
```

---

## 5. Alur Kerja Pipeline (Workflow Lifecycle)

Pipeline `.github/workflows/deploy.yml` berjalan secara otomatis dengan alur:

```text
[Push / PR to main]
        │
        ▼
   [Job 1: test]
   - Setup PHP 8.3 & Ekstensi
   - Cache Composer & NPM
   - composer install
   - npm ci && npm run build
   - php artisan test (SQLite :memory:)
        │
        ├─► (Jika Gagal: Pipeline berhenti, kode tidak dideploy)
        │
        ▼ (Jika Berhasil & Event adalah Push ke 'main')
   [Job 2: deploy]
   - SSH ke VPS (/var/www/thortech/hadiryuk)
   - git pull origin main
   - composer install --no-dev --optimize-autoloader
   - npm ci && npm run build
   - php artisan migrate --force
   - php artisan optimize:clear && cache (config, route, view)
   - sudo systemctl restart hadiryuk
        │
        ▼
   [Live di https://hadiryuk.thortech.shop via Cloudflare Tunnel]
```

---

## 6. Verifikasi Pasca Deployment

Setelah pipeline berhasil berjalan:
1. Periksa log GitHub Actions di tab **Actions** repositori.
2. Periksa status service di VPS:
   ```bash
   sudo systemctl status hadiryuk
   ```
3. Uji endpoint produksi:
   ```bash
   curl -I https://hadiryuk.thortech.shop
   ```
