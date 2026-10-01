# 08 — Rencana Migrasi ke Target (Express→Laravel, SolidJS→Svelte)

> Bagian dari single source of truth. Kode lama tidak diubah kecuali fix kritis. Setiap fase selesai → update [00-status](00-status.md) + centang checklist di bawah.

## Prinsip
- Kontrak dibekukan: URI, payload, dan alur di [05](05-api-reference.md)/[04](04-alur-bisnis.md) tidak berubah selama migrasi.
- Backend dulu, frontend kemudian. Frontend Svelte dikembangkan menghadap API Laravel baru (atau fixture payload lama yang identik).
- Data lama dipertahankan: nama tabel/kolom sama ([03](03-database.md)), migrasi data = dump + import + rewrite URL `/uploads/*` → `/storage/*`.

## Fase 0 — Fondasi (sebelum kode)
- [ ] Selaraskan `.env.docker.example` ke tabel kanonis [06](06-deployment.md) §6.4 (`APP_URL`, `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, `APP_KEY`, `DB_*`, `MAIL_*`, `SESSION_*`, `SEED_ADMIN_*`; hapus `PUBLIC_APP_URL`/`DATABASE_URL`/`BETTER_AUTH_*`).
- [ ] Tulis ulang `Dockerfile` (stage `app` = PHP-FPM Laravel, `web` = nginx + hasil build SvelteKit) dan sesuaikan target `Makefile` yang memanggil `node dist/*` → `php artisan ...`.
- [ ] Bekukan fixture: simpan 1 contoh response `GET /api/v1/home` + tiap endpoint [05](05-api-reference.md) dari backend lama sebagai oracle test Fase 1.
- Selesai bila: `docker compose build` menghasilkan image `amd64`-benar di server dan `arm64` di Mac (lihat [06](06-deployment.md) §6.3).

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
2. `make migrate && make seed` di server → smoke test [06](06-deployment.md) §6.6 → checklist §6.7.
3. Aktifkan tunnel, matikan stack lama, backup pertama.
- Selesai bila: homepage + 1 order paket + 1 mockup + login admin + 1 upload berjalan di domain produksi, backup tersimpan.
