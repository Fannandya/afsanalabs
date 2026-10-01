# 06 — Deployment Docker Multi-Arch (Mac ARM64 → Ubuntu AMD64)

> Bagian dari single source of truth ([README](README.md)). Pola compose mengikuti `docker-compose.prod.yml` (service `app`, `nginx`, `mysql`, `cloudflared`); isi image `app`/`web` mengikuti stack target Laravel + SvelteKit.

## 6.1 Dua mesin

| Mesin | Peran | Arch |
|---|---|---|
| Mac Apple Silicon (M1/M2/…) | Dev + opsional build image | `arm64` (`aarch64`) |
| Ubuntu Server (VPS) | Produksi | `amd64` (`x86_64`) |

Cek arch kapan saja: `uname -m` (Mac → `arm64`, Ubuntu → `x86_64`) dan `docker inspect <image> --format='{{.Architecture}}'`.

## 6.2 Prinsip: satu compose, dua arch
Semua base image yang dipakai bermanifest **multi-arch** (varian `arm64` + `amd64` resmi): `php:fpm`, `nginx:alpine`, `mysql:8.4`, `cloudflare/cloudflared`. Docker otomatis menarik varian sesuai mesin — jadi **jangan pasang `platform:`** di compose; pin manual justru memaksa emulasi QEMU yang lambat (terutama MySQL di Mac).
- Dev lokal (Mac): `docker-compose.yml` — hanya service `mysql` (Laravel via `php artisan serve`, SvelteKit via `npm run dev`). Jalan native `arm64`, tanpa emulasi.
- Produksi (Ubuntu): `docker-compose.prod.yml` — `app` (PHP-FPM Laravel) + `nginx` (file statis SvelteKit + proxy PHP + `/storage`) + `mysql` + `cloudflared` (profil `tunnel`). Jalan native `amd64`.
- Volume (`mysql-data`, `uploads-data`) bersifat lokal per mesin dan portable lintas arch untuk versi image yang sama — tetapi tidak dishare; pindahkan data via backup SQL, bukan dengan menyalin volume.

## 6.3 Jebakan utama: image custom hasil build Mac tidak jalan di server
`docker compose build` di Mac menghasilkan image **`arm64`**; dijalankan di Ubuntu `amd64` → error `exec format error`. Ada dua alur resmi — pilih satu:

**Alur A — build di server (disarankan, tanpa urusan arch).**
```bash
# di Ubuntu: tarik kode, siapkan env, build + jalan native amd64
git pull
make env   # sekali saja, lalu isi .env.docker
make init  # build + DB + migrasi + seed (lihat §6.5)
```
Tidak ada flag platform apa pun — build dan run sama-sama `amd64`.

**Alur B — build di Mac, deploy image ke server.**
```bash
# sekali saja di Mac: aktifkan builder multi-arch
docker buildx create --name multi --use
# build image app untuk AMD64 lalu push ke registry
docker buildx build --platform linux/amd64 \
  -t registry-contoh/jasa-app:latest --target app --push .
docker buildx build --platform linux/amd64 \
  -t registry-contoh/jasa-web:latest --target web --push .
# di Ubuntu: ganti build: dengan image: registry-... lalu
docker compose -f docker-compose.prod.yml --env-file .env.docker pull
docker compose -f docker-compose.prod.yml --env-file .env.docker up -d
```
Varian: `--platform linux/amd64,linux/arm64` bila image yang sama juga ingin dijalankan di Mac. Jangan jalankan image `amd64` di Mac via emulasi untuk MySQL/PHP — lambat; untuk test cepat cukup andalkan varian `arm64`.

## 6.4 Env produksi (`.env.docker`)
Bentuk file sama seperti `.env.docker.example` yang ada; nilainya mengikuti stack Laravel (kanonis — lihat [00-status](00-status.md) No.14):

| Var | Isi |
|---|---|
| `APP_URL` | `https://domain-anda` (root Laravel, URL absolut) |
| `FRONTEND_URL` | `https://domain-anda` (dipakai CORS + link reset password) |
| `SANCTUM_STATEFUL_DOMAINS` | domain frontend tanpa skema (mis. `domain-anda`) — wajib agar cookie Sanctum terkirim |
| `SESSION_LIFETIME` | `120` (sesi admin idle-timeout 120 menit) |
| `SESSION_SECURE_COOKIE` | `true` di production (cookie hanya via HTTPS) |
| `DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD` | `mysql`, `3306`, `jasa_website`, `***`, `***` (di compose: host `mysql`; wajib diganti dari contoh) |
| `MYSQL_ROOT_PASSWORD` | wajib diganti dari contoh |
| `APP_KEY` | `php artisan key:generate` (pengganti `BETTER_AUTH_SECRET`) |
| `SEED_ADMIN_EMAIL/SEED_ADMIN_PASSWORD` | admin awal (`make seed`, idempotent) |
| `MAIL_*` | `MAIL_MAILER/HOST/PORT/USERNAME/PASSWORD/FROM_ADDRESS`; kosong = notifikasi email mati, in-app tetap jalan |
| `TUNNEL_TOKEN` | token Cloudflare Zero Trust; stack inti jalan tanpa ini, tunnel via `make tunnel-up` |

Catatan: `.env.docker.example` di repo masih memuat var era Express (`BETTER_AUTH_*`, `DATABASE_URL`, `PUBLIC_APP_URL`) — hapus ketiganya saat penyelarasan. Penyelesaian tercatat sebagai G6 di [00-status](00-status.md) dan dikerjakan di [08](08-migrasi.md) Fase 0.

## 6.5 Perintah (`Makefile`, konsep tidak berubah)
`make env|init|build|up|down|restart|ps|logs|migrate|seed|admin|backup|upgrade|shell|mysql|tunnel-up|tunnel-down|preview` — konsep sama, hanya perintah di dalamnya mengikuti Laravel:
- `secret` era Express (generate `BETTER_AUTH_SECRET`) digantikan `php artisan key:generate` (`APP_KEY`).
- `admin` tetap: penjelasan bahwa admin dibuat via `make seed` dari `SEED_ADMIN_*`.
- `migrate` → `exec app php artisan migrate --force` (dulu `node dist/db/migrate.js`).
- `seed` → `exec app php artisan db:seed --force` (idempotent via `firstOrCreate`).
- `shell` → `exec app sh` (image PHP tetap punya `sh`).
- Tambahan yang disarankan: target `build-amd64` berisi perintah `buildx --platform linux/amd64 --push` dari §6.3 untuk Alur B.

## 6.6 Verifikasi
1. `make ps` — semua `healthy`/`running` (`app` punya healthcheck ke `/health`, `mysql` via `mysqladmin ping`).
2. `docker inspect <img> --format='{{.Architecture}}'` — di Ubuntu harus `amd64`, di Mac `arm64`.
3. Buka `https://domain-anda` → homepage render dari satu `GET /api/v1/home`; login `/admin/login` (wajib ambil CSRF cookie dulu — lihat tabel Auth di [05-api-reference.md](05-api-reference.md)) → sesi idle-timeout 120 menit.
4. `make preview` (buka port nginx sementara) hanya untuk debug tanpa tunnel; tutup lagi setelah selesai.

## 6.7 Checklist sebelum go-live
- [ ] `.env.docker` terisi semua (sandi diganti, `APP_KEY` digenerate, `SEED_ADMIN_PASSWORD` kuat).
- [ ] Image di server ber-arch `amd64` (bukan hasil copy dari Mac tanpa buildx).
- [ ] Migrasi + seed sukses (`make migrate && make seed`).
- [ ] `TUNNEL_TOKEN` valid bila pakai `tunnel-up`; kalau tidak, port nginx hanya dibuka seperlunya.
- [ ] Backup awal jalan (`make backup` → folder `backups/`), lalu jadwalkan berkala.
