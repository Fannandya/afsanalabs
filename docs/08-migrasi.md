# 08 — Rencana Migrasi ke Target (Express→Laravel, SolidJS→Svelte)

> Bagian dari single source of truth. Kode lama tidak diubah kecuali fix kritis. Setiap fase selesai → update [00-status](00-status.md) + centang checklist di bawah.

## Prinsip
- Kontrak dibekukan: URI, payload, dan alur di [05](05-api-reference.md)/[04](04-alur-bisnis.md) tidak berubah selama migrasi.
- Backend dulu, frontend kemudian. Frontend Svelte dikembangkan menghadap API Laravel baru (atau fixture payload lama yang identik).
- Data lama dipertahankan: nama tabel/kolom sama ([03](03-database.md)), migrasi data = dump + import + rewrite URL `/uploads/*` → `/storage/*`.

## Fase 0 — Fondasi (sebelum kode)
- [ ] Siapkan akun cPanel sesuai [06](06-deployment.md) §6.1 (PHP 8.3+, ekstensi, MySQL, AutoSSL).
- [ ] Siapkan `.env` dari `backend/.env.example` mengikuti tabel kanonis [06](06-deployment.md) §6.2 (tanpa var legacy `PUBLIC_APP_URL`/`DATABASE_URL`/`BETTER_AUTH_*`).
- [ ] Bekukan fixture: simpan 1 contoh response `GET /api/v1/home` + tiap endpoint [05](05-api-reference.md) dari backend lama sebagai oracle test Fase 1.
- Selesai bila: PHP + MySQL + HTTPS siap di cPanel (lihat [06](06-deployment.md) §6.5).

## Fase 1 — Backend Laravel
1. Migrasi + seeder ([03](03-database.md) §3.8) → import data produksi lama → rewrite URL gambar ke `/storage/*`.
2. Sanctum auth (login/logout/reset + ganti password `PATCH /api/v1/admin/password`, sesi idle-timeout 120 menit) + `auth:sanctum` di semua `/admin/*` + `throttle:10,1` untuk 3 endpoint auth.
3. API publik ([02](02-backend-logic.md) §2.2–§2.4): home gabungan, katalog, form + tracking + WhatsApp.
4. Admin CMS: base `CrudController`/`SingletonController` + 15 CRUD + 4 singleton + section-headers + orders/messages/notifications + uploads + `NotifyService` + cleanup command + `HealthController`.
5. Test minimum [00-status](00-status.md) hijau.
- Selesai bila: setiap endpoint [05](05-api-reference.md) mengembalikan payload identik fixture Fase 0 (kecuali `422` vs `400` validasi, URL `/storage/*`, envelope paginasi `{data,page,limit}`, dan `429` throttle).

## Fase 2 — Frontend SvelteKit
1. Shell + routing + layout ([01](01-ringkasan-proyek.md) struktur SvelteKit) + `lib/api.ts` + CSRF.
2. Homepage: 9 section + kondisional About/Team/Logo, `<svelte:head>` SEO.
3. Alur publik: portofolio + filter, konsultasi, pesan (+WhatsApp redirect), mockup, lacak.
4. Admin: login, 13 halaman konten (`hero`, `section-headers`, `value-props`, `process-steps`, `objection`, `services`, `portfolio`, `about-timeline`, `team`, `clients`, `pricing`, `mockup-offer`, `faq`) + settings (`business`, `seo`, `testimonials`) + orders/messages/notifications, upload, Toast/ConfirmDialog.
- Selesai bila: kriteria "Selesai bila" di `features/` terpenuhi + tidak ada teks CMS yang di-hardcode (cek lawan [07-cms](07-cms.md)).

## Fase 3 — Cutover produksi
1. Migrasi data final + verifikasi URL gambar.
2. `php artisan migrate --force && php artisan db:seed --force` di hosting → smoke test [06](06-deployment.md) §6.5 → checklist §6.8.
3. Arahkan domain + aktifkan AutoSSL, backup pertama.
- Selesai bila: homepage + 1 order paket + 1 mockup + login admin + 1 upload berjalan di domain produksi, backup tersimpan.
