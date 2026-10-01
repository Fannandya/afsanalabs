# 00 — Status, Keputusan & Gap (sumber kebenaran status proyek)

> Bagian dari single source of truth (`docs/`). File ini adalah satu-satunya tempat mencatat **keputusan yang dikunci**, **status spec-vs-kode**, dan **gap terbuka**. Jangan catat hal yang sama di file lain.

## Aturan main
1. `docs/` adalah acuan pengembangan. Bila bertentangan dengan dokumen root lama (`PRD.md`, `ARCHITECTURE.md`, `SCHEMA.md`, `CMS.md`, `RULES.md`, `DESIGN.md`), `features/`, atau kode lama — **yang berlaku adalah `docs/`**.
2. Dokumen root lama + `features/` tetap dibaca sebagai **referensi sejarah/acceptance** (alasan keputusan, kriteria "Selesai bila"), bukan sebagai spec yang diikuti bila berbeda dari `docs/`.
3. Setiap perubahan perilaku wajib update file `docs/` terkait **di commit yang sama** dengan kode; keputusan baru dicatat di § Keputusan Terkunci.
4. Kode lama (Express + SolidJS) tidak diubah kecuali fix kritis — semua pengembangan baru mengikuti target di `docs/` (lihat [08-migrasi.md](08-migrasi.md)).

## Keputusan yang dikunci (hanya boleh diubah via bagian ini + persetujuan pemilik produk)
1. SPA client-side routing; pola **preview + Lihat Lebih Banyak** untuk konten yang bisa tumbuh.
2. Mockup berbayar = produk terpisah (`order_type='mockup'`); potongan ke paket penuh dihitung manual admin.
3. Pembayaran manual transfer + verifikasi admin. Paket = tanpa bayar di muka (lanjut WhatsApp); mockup = bayar di muka (instruksi transfer).
4. Storage gambar = disk lokal hosting, bukan S3.
5. Kategori portofolio = tabel `categories`, bukan enum.
6. API publik pesanan hanya kenal `tracking_code` (`ORD-XXXXXX`), bukan `id`.
7. Auth = Laravel Sanctum, cookie httpOnly, sesi idle-timeout 120 menit (`SESSION_LIFETIME=120`, sliding Laravel); satu akun admin, tanpa role.
8. Backend = Laravel (PHP), frontend = SvelteKit SPA statis, DB = MySQL 8 (skema/tabel tidak berubah).
9. Deploy = shared hosting cPanel, satu domain (frontend statis + API Laravel + `/storage` dalam satu docroot `backend/public`, lihat [06](06-deployment.md)).
10. Visual = tema "Vivid Azure" rev.3 + struktur `redesign ui/` (detail di `DESIGN.md` — tetap jadi referensi visual).
11. Sesi admin = idle-timeout 120 menit Laravel, bukan absolut fixed. Idle 2 jam → logout.
12. `throttle:10,1` berlaku untuk 4 form publik + 3 auth (`contact`, `consultations`, `orders`, `mockup-requests`, `admin/login`, `admin/forgot-password`, `admin/reset-password`). Lebih → `429`.
13. Paket nonaktif → `422 VALIDATION_ERROR`; `mockup_offer` belum diseed → `404`. Tidak ada `400` untuk kedua kasus ini.
14. Env kanonis: `APP_URL`, `FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`, `DB_*`, `SESSION_*`, `MAIL_*`, `SEED_ADMIN_*`, `APP_KEY` (lihat tabel [06](06-deployment.md) §6.2). `PUBLIC_APP_URL` / `DATABASE_URL` / `BETTER_AUTH_*` dihapus (legacy).
15. Ganti kata sandi = `PATCH /api/v1/admin/password` (`current_password` + `password|min:8|confirmed`). Masuk kontrak beku [05](05-api-reference.md).
16. 8 kolom gambar kanonis: `hero_content.image_url`, `portfolios.image_url`, `team_members.photo_url`, `client_logos.logo_url`, `about_timeline_items.image_url`, `mockup_offer.image_url`, `seo_settings.og_image_url`, `business_settings.footer_map_url`.
17. Mapping notifikasi: `pesanan_baru→notify_new_order`, `permintaan_mockup→notify_mockup_request`, `pesan_kontak→notify_contact_message`, `konsultasi_baru→notify_consultation`.
18. Paginasi admin: query `?page&limit` (default 20, maks 100), respons `{ data, page, limit }`. `limit` dipetakan ke `paginate($limit)` Laravel.
19. Pengecualian slug beku: `price_packages→/admin/packages`, `about_timeline_items→/admin/about-timeline`. Selain itu noun jamak kebab-case.
20. `section_headers` = 9 key tetap (`value_props`, `process`, `objection`, `services`, `portfolio`, `pricing`, `faq`, `about`, `team`).

## Status spec-vs-kode (per 2026-10-01)

| Area | Spec (`docs/`) | Kode aktual | Status |
|---|---|---|---|
| Backend API | Laravel, [02](02-backend-logic.md) + [05](05-api-reference.md) | Express (`backend/src`) | ⏳ Belum migrasi → [08](08-migrasi.md) Fase 1 |
| Frontend | SvelteKit SPA, [01](01-ringkasan-proyek.md) | SvelteKit (`frontend/src`, adapter-static, SSR off) | ✅ Selaras — publik 6 route + admin 20+ route, build statis lolos |
| Skema/tabel DB | [03](03-database.md) — sama dengan `SCHEMA.md` | MySQL via Drizzle, isi sesuai | ✅ Selaras (nama tabel/kolom tetap saat migrasi) |
| Kontrak API (URI + payload) | [05](05-api-reference.md) | Express, sesuai | ✅ Selaras (kontrak dibekukan, implementasi diganti) |
| Alur bisnis | [04](04-alur-bisnis.md) | Sesuai | ✅ Selaras |
| CMS mapping | [07-cms.md](07-cms.md) | Sesuai `CMS.md` | ✅ Selaras |
| Deploy/dev | [06](06-deployment.md) | Tanpa Docker: dev lokal `artisan serve` + `npm run dev` | ✅ Selaras (deploy cPanel, panduan di [06](06-deployment.md)) |

## Gap terbuka (jangan diisi asal — klarifikasi dulu)

| ID | Gap | Asal | Status |
|---|---|---|---|
| G1 | Eyebrow "Apa yang Kami Maksud" berbunyi "…ROOFEL" (diduga sisa template) | `DESIGN.md §6` | ⏳ Konfirmasi pemilik produk |
| G2 | Isi topnav vs footer nav belum sinkron | `DESIGN.md §6`, `CMS.md §2.1` | ⏳ Keputusan konten (rekomendasi: satu set link ke section yang ada) |
| G3 | Kartu paket tampilkan angka harga atau tidak (`show_price`) | `DESIGN.md §6` | ⏳ Keputusan bisnis (skema sudah siap dua-duanya) |
| G4 | Jawaban 4 FAQ belum ditulis (desain hanya ada pertanyaan) | `DESIGN.md §6`, `CMS.md §2.10` | ⏳ TODO konten sebelum go-live |
| G5 | `reference_price_cards` bisa basi (harga pihak ketiga) | `CMS.md §2.5` | ⚠️ Proses: admin update berkala + `note_text` wajib menyebut tanggal cek |
| G6 | Deploy diputus ke shared hosting cPanel (bukan Docker) | Keputusan [00-status](00-status.md) No.9 | ✅ Selesai: file Docker dihapus, panduan cPanel di [06](06-deployment.md) |
| G7 | `ARCHITECTURE.md` menyebut `OrderConfirmation`/`/pesan/konfirmasi` yang tidak ada di kode | Audit `docs/` | ✅ Diputus: ikut kode (hanya `/pesan`) sampai ada spec baru |

## Testing minimum (wajib hijau sebelum klaim selesai)
Backend: uniqueness `tracking_code` (termasuk retry collision), transisi status order paket (`menunggu_konfirmasi→dalam_pengerjaan→selesai/dibatalkan`) + mockup (`menunggu_pembayaran→dalam_pengerjaan→selesai/dibatalkan`), guard `auth:sanctum` menolak tanpa sesi, upload menolak non-gambar/>2MB, validasi → 422 + `fields`, throttle → 429 setelah 11x/menit, ganti password 422 bila `<8` char. Prioritas: alur order/auth di atas halaman statis.
