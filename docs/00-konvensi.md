# 00 — Konvensi Pengembangan (wajib diikuti)

> Bagian dari single source of truth. Diserap dari `RULES.md` dan diterjemahkan ke stack target (Laravel + Svelte). Bila berbeda dari `RULES.md`, yang berlaku adalah file ini.

## 1. Prinsip umum
- Jangan bangun abstraksi/fitur di luar yang diminta spec. Tiga baris mirip lebih baik daripada helper prematur.
- Yang disebut "dikelola admin" **wajib** bersumber dari database — termasuk heading/subtitle tiap section (`section_headers`). Tidak ada teks CMS yang di-hardcode di frontend/backend.
- Validasi hanya di boundary (request masuk API). Jangan validasi ulang data yang sudah dipercaya dari database sendiri.
- Komentar hanya untuk alasan non-obvious (mis. kenapa tracking code di-retry). Jangan menjelaskan APA yang dilakukan kode.
- Jangan menebak konten yang belum jelas — catat sebagai gap di [00-status.md](00-status.md), jangan diisi asal.

## 2. Penamaan
- **Database:** `snake_case`, tabel jamak (`portfolios`, `services`); pengecualian tabel bawaan Laravel (`users`, `sessions`, `password_reset_tokens`).
- **Laravel:** Model `PascalCase` singular (`PricePackage`), controller `PascalCase + Controller`, FormRequest `StoreXRequest`/`UpdateXRequest`, trait/concern di `Concerns/`, service di `app/Services`, migrasi `timestamp_nama_snake`, Artisan command `kebab-case`.
- **SvelteKit:** komponen `PascalCase.svelte`, route folder `lowercase` (`routes/pesan/+page.svelte`), `lib/*.ts` `camelCase`.
- **API route:** noun jamak, kebab-case (`/api/v1/admin/business-settings`). Slug yang sudah didefinisikan di [05](05-api-reference.md) tidak diganti seenaknya. Pengecualian beku (lihat [00-status](00-status.md) No.19): tabel `price_packages` tetap `/admin/packages`, tabel `about_timeline_items` tetap `/admin/about-timeline`.

## 3. Navigasi SPA
- Pindah "halaman" wajib via `goto()`/`{@html ...}`-link SvelteKit (`$app/navigation`), bukan `<a href>` reload penuh.
- Konten yang bisa tumbuh (portofolio): preview `is_featured` di beranda + tombol "Lihat Lebih Banyak" ke halaman penuh. Jangan render seluruh data di beranda.
- Section kecil-tetap (Layanan, Harga, FAQ): tampil lengkap di beranda, tanpa halaman "lihat lebih banyak" (hindari over-engineering).

## 4. API & validasi
- Setiap endpoint ber-body wajib FormRequest sebelum controller; gagal → 422 + pesan field-level (`{ error: { message, code: "VALIDATION_ERROR", fields } }`).
- `throttle:10,1` (= 10 request/menit per IP, lebih → `429`) wajib untuk `POST /api/v1/contact`, `/consultations`, `/orders`, `/mockup-requests` dan `POST /api/v1/admin/login`, `/forgot-password`, `/reset-password`.
- Semua `/api/v1/admin/*` wajib `auth:sanctum` — tanpa pengecualian, termasuk read-only.
- List tak terbatas (`orders`, `contact-messages`, `consultations`, `notifications`) wajib paginasi `?page&limit` → `{ data, page, limit }`; jangan `SELECT` tanpa limit.
- Jangan pernah ekspos `orders.id` ke publik — hanya `tracking_code`.

## 5. Keamanan
- Password/reset sepenuhnya via `Auth`/`Password` broker + `Hash`. Tanpa hashing manual di controller.
- Query selalu via Eloquent/query builder (parameterized) — tanpa raw SQL + input user.
- Upload: magic-bytes + `max:2048` + nama UUID + folder whitelist; simpan di disk `public` (tidak executable).
- Cookie sesi: `httpOnly`, `secure` di production, `sameSite=lax` minimum.
- Jangan log password/token/cookie sesi. `.env` tidak pernah di-commit; `.env.example` hanya nama variabel.

## 6. Error & feedback
- Satu exception handler terpusat; envelope `{ data }` / `{ error: { message, code?, fields? } }`; tanpa stack trace di production.
- Pesan ke pengguna Bahasa Indonesia yang jelas.
- Semua form admin: Toast sukses/gagal; hapus wajib `ConfirmDialog`; nonaktifkan-tanpa-hapus pakai `is_active`, bukan hard delete.

## 7. Data
- Satu perubahan skema = satu migrasi = satu commit terpisah dari fitur.
- Daftar kolom gambar (`$imageFields`) wajib sama persis dengan daftar kanonis 8 kolom di [00-status](00-status.md) No.16 dan sinkron dengan `CleanupOrphanedUploads` (lihat [02](02-backend-logic.md) §2.6/§2.12).

## 8. Git & environment
- Jangan commit `.env`, isi `storage/app/public`, `node_modules`, `vendor`.
