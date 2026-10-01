# 06 — Deployment Shared Hosting (cPanel), Satu Domain

> Bagian dari single source of truth ([README](README.md)). Satu domain melayani frontend statis + API Laravel + `/storage` (tanpa CORS lintas domain, tanpa Docker).

## 6.1 Arsitektur

| Bagian | Lokasi di hosting | Keterangan |
|---|---|---|
| Kode Laravel | `~/backend` (di luar docroot) | Hasil `git clone`, bukan tempat upload publik |
| Docroot domain | `~/backend/public` | Diisi juga hasil build SvelteKit (`frontend/build/*` disalin ke sini) |
| Database | MySQL via cPanel (MySQL Database Wizard) | `DB_HOST=localhost` (socket), tanpa akses root |
| PHP | Selector cPanel (`lsphp` 8.3+) | Ekstensi wajib: `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `bcmath`, `curl` |
| TLS | AutoSSL cPanel | Wajib aktif sebelum go-live (cookie `Secure`) |
| Cron | cPanel → Cron Jobs | Cleanup upload mingguan (lihat §6.7) |

Alur request (Apache, lihat `.htaccess` di §6.6): file ada → serve langsung; `/api|/sanctum|/health|/up` → `index.php` (Laravel); selain itu → `index.html` (SPA fallback, client-side routing).

## 6.2 Env produksi (`~/backend/.env`)

Salin dari `backend/.env.example`, lalu sesuaikan (kanonis — lihat [00-status](00-status.md) No.14):

| Var | Isi |
|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` (wajib) |
| `APP_URL` / `FRONTEND_URL` | `https://domain-anda` (sama, satu domain) |
| `SANCTUM_STATEFUL_DOMAINS` | `domain-anda` tanpa skema — wajib agar cookie Sanctum terkirim |
| `APP_KEY` | `php artisan key:generate --show` (tempel manual, jangan commit) |
| `DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD` | `localhost`, `3306`, `user_db` (dari wizard), `user_dbuser`, `***` |
| `SESSION_DRIVER` / `SESSION_LIFETIME` / `SESSION_SECURE_COOKIE` | `database` / `120` / `true` |
| `SEED_ADMIN_EMAIL/SEED_ADMIN_PASSWORD` | admin awal (`db:seed`, idempotent) |
| `MAIL_*` | SMTP akun cPanel (`MAIL_MAILER=smtp`, host `mail.domain-anda`); kosong = notifikasi email mati, in-app tetap jalan |

`.env` tidak pernah di-commit dan tidak boleh bisa diakses via web (di luar docroot, aman).

## 6.3 Langkah deploy awal

1. Buat database + user via **MySQL Database Wizard**, catat nama DB/user/password.
2. Upload/clone repo ke `~/backend` (cPanel **Git Version Control** atau SSH: `git clone … ~/backend`).
3. Di terminal cPanel (atau SSH), dari `~/backend`:
   ```bash
   cp .env.example .env   # lalu isi sesuai §6.2
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan storage:link
   php artisan migrate --force
   php artisan db:seed --force
   ```
4. Build frontend **di lokal** (tanpa `VITE_API_BASE` agar relatif ke domain yang sama):
   ```bash
   cd frontend && npm ci && npm run build
   ```
   Salin isi `frontend/build/` ke `~/backend/public/` (timpa `index.html` bawaan Laravel bila ada).
5. Pastikan `.htaccess` docroot mengikuti §6.6 (API + SPA fallback).
6. Aktifkan **AutoSSL** untuk domain, paksa HTTPS.
7. Buka `https://domain-anda` → homepage dari satu `GET /api/v1/home`; login `/admin/login` → sesi idle-timeout 120 menit.

## 6.4 Update berikutnya

```bash
cd ~/backend && git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
# bila ada perubahan frontend: rebuild lokal lalu salin ulang frontend/build/* ke public/
```

## 6.5 Verifikasi

1. `https://domain-anda/health` → `{"status":"ok"}`.
2. `https://domain-anda/api/v1/home` → payload homepage (bukan halaman HTML).
3. Route SPA langsung (`/portofolio`, `/admin/login`) render tanpa 404 Apache (fallback `index.html` jalan).
4. Login admin → kelola 1 konten → upload 1 gambar → tampil via `/storage/...`.

## 6.6 `.htaccess` docroot (API + SPA fallback)

Simpan sebagai `~/backend/public/.htaccess` (ganti bawaan Laravel):

```apache
DirectoryIndex index.html index.php

<IfModule mod_rewrite.c>
    RewriteEngine On

    # 1. File/dir yang ada diserve langsung (aset SvelteKit, /storage/*, index.html).
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # 2. API/auth/health ke Laravel.
    RewriteCond %{REQUEST_URI} ^/(api|sanctum|health|up)(/|$)
    RewriteRule ^ index.php [L]

    # 3. Sisanya ke SPA (client-side routing).
    RewriteRule ^ index.html [L]
</IfModule>
```

## 6.7 Operasional (cron + backup)

Cron mingguan (cPanel → Cron Jobs), ganti path sesuai akun:

```cron
0 3 * * 0 cd ~/backend && /usr/local/bin/php artisan uploads:cleanup --apply >> ~/uploads-cleanup.log 2>&1
```

Backup: database via cPanel **Backup Wizard** (jadwalkan), file upload (`~/backend/storage/app/public`) ikut dalam full backup akun. Sebelum update besar: backup manual dulu, lalu uji `migrate` di hasil restore bila memungkinkan.

## 6.8 Checklist sebelum go-live

- [ ] PHP 8.3+ + semua ekstensi §6.1 aktif; AutoSSL aktif + paksa HTTPS.
- [ ] `.env` terisi semua (`APP_DEBUG=false`, `APP_KEY` digenerate, sandi diganti, `SEED_ADMIN_PASSWORD` kuat).
- [ ] Migrasi + seed sukses; `storage:link` ada (`public/storage` mengarah ke storage).
- [ ] `.htaccess` §6.6 terpasang; verifikasi §6.5 hijau semua.
- [ ] Backup awal tersimpan, cron cleanup jalan.
