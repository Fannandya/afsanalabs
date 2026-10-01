# 07 — CMS: Section → Database → Admin → API

> Bagian dari single source of truth. Satu-satunya peta resmi konten homepage. Prinsip: **tidak ada teks statis di kode frontend** — semua yang terlihat di homepage berasal dari tabel di bawah dan dikelola lewat halaman admin yang tercantum.

## Peta utama

> Semua endpoint di bawah berprefix `/api/v1` (ditulis penuh agar sama dengan [05](05-api-reference.md)).

| # | Section homepage | Tabel sumber | Halaman admin (SvelteKit) | Endpoint admin | Tampil publik via |
|---|---|---|---|---|---|
| 0 | TopNavBar & Footer | `business_settings`, `nav_links`, `footer_legal_links` | `/admin/settings/business` (satu halaman kelola 3 tabel) | `GET/PATCH /api/v1/admin/business-settings`, CRUD `/api/v1/admin/nav-links`, `/api/v1/admin/footer-legal-links` | `GET /api/v1/home` (`businessSettings`, `navLinks.topnav/footer`, `footerLegalLinks`) |
| 1 | Hero | `hero_content` (singleton) | `/admin/content/hero` | `GET/PATCH /api/v1/admin/hero` | `home.hero` |
| 2 | Apa yang Kami Maksud | `section_headers['value_props']` + `value_props` | `/admin/content/value-props` | `PATCH /api/v1/admin/section-headers/value_props`, CRUD `/api/v1/admin/value-props` | `home.sectionHeaders.value_props` + `home.valueProps` |
| 3 | Proses Kerja | `section_headers['process']` + `process_steps` | `/admin/content/process-steps` | `PATCH /api/v1/admin/section-headers/process`, CRUD `/api/v1/admin/process-steps` | `home.sectionHeaders.process` + `home.processSteps` |
| 4 | Pra-Keputusan | `section_headers['objection']` + `objection_questions` + `reference_price_cards` | `/admin/content/objection` | `PATCH /api/v1/admin/section-headers/objection`, CRUD `/api/v1/admin/objection-questions`, `/api/v1/admin/reference-price-cards` | `home.sectionHeaders.objection` + `home.objectionQuestions` + `home.referencePriceCards` |
| 5 | Layanan Kami | `section_headers['services']` + `services` | `/admin/content/services` | `PATCH /api/v1/admin/section-headers/services`, CRUD `/api/v1/admin/services` | `home.sectionHeaders.services` + `home.services` |
| 6 | Portofolio (preview) | `section_headers['portfolio']` + `portfolios` + `categories` | `/admin/content/portfolio` | `PATCH /api/v1/admin/section-headers/portfolio`, CRUD `/api/v1/admin/portfolios`, `/api/v1/admin/categories` | `home.featuredPortfolios` (hanya `is_featured`); lengkap via `GET /api/v1/portfolios?category=` + `GET /api/v1/categories` |
| 6b | Tentang Kami (timeline) | `section_headers['about']` + `about_timeline_items` | `/admin/content/about-timeline` | `PATCH /api/v1/admin/section-headers/about`, CRUD `/api/v1/admin/about-timeline` (beku, tabel `about_timeline_items`) | `home.aboutTimeline` — **tidak dirender bila kosong** |
| 6c | Tim Kami | `section_headers['team']` + `team_members` | `/admin/content/team` | `PATCH /api/v1/admin/section-headers/team`, CRUD `/api/v1/admin/team-members` | `home.teamMembers` — **tidak dirender bila kosong** |
| 6d | Logo Klien | `client_logos` (tanpa header) | `/admin/content/clients` | CRUD `/api/v1/admin/client-logos` | `home.clientLogos` — **tidak dirender bila kosong** |
| 7 | Paket & Harga | `section_headers['pricing']` + `price_packages` | `/admin/content/pricing` | `PATCH /api/v1/admin/section-headers/pricing`, CRUD `/api/v1/admin/packages` (beku, tabel `price_packages`) | `home.sectionHeaders.pricing` + `home.pricePackages` |
| 8 | Tawaran Mockup | `mockup_offer` (singleton) | `/admin/content/mockup-offer` | `GET/PATCH /api/v1/admin/mockup-offer` | `home.mockupOffer` |
| 9 | FAQ | `section_headers['faq']` + `faqs` | `/admin/content/faq` | `PATCH /api/v1/admin/section-headers/faq`, CRUD `/api/v1/admin/faqs` | `home.sectionHeaders.faq` + `home.faqs` |
| — | SEO (bukan section visual) | `seo_settings` (singleton) | `/admin/settings/seo` | `GET/PATCH /api/v1/admin/seo-settings` | `home.seoSettings` → `<svelte:head>` |
| — | Testimoni (di `/portofolio`) | `testimonials` | `/admin/settings/testimonials` | CRUD `/api/v1/admin/testimonials` | `GET /api/v1/testimonials` |

Halaman `/admin/content/section-headers` tetap ada sebagai jalan pintas mengedit semua header sekaligus (satu `GET /api/v1/admin/section-headers` + `PATCH` per key).

## Aturan khusus per section (normatif)
- **Semua list CRUD**: pola sama — daftar item → Tambah → form tambah/ubah → `ConfirmDialog` sebelum hapus → Toast. `sort_order` via drag-and-drop/angka; `is_active` untuk sembunyikan tanpa hapus.
- **Portofolio**: toggle `is_featured` = "Tampilkan di Beranda" (sarankan 3–4 sesuai grid 3-kolom; tanpa hard-limit di DB). `is_active=false` = sembunyi di mana-mana.
- **Harga**: `price` boleh kosong + `show_price` mengontrol tampil (lihat G3 di [00-status](00-status.md)); hanya boleh satu paket `is_recommended` (enforcement di FormRequest + transaksi, bukan constraint DB — lihat [02](02-backend-logic.md) §2.9); `cta_action='order'` → `/pesan?package=:id`, `'contact'` → `/konsultasi`.
- **Mockup**: `price` wajib; nilainya di-snapshot ke `orders.mockup_fee` saat dipesan (harga lama tidak berubah walau offer diedit kemudian).
- **Nav**: `target` wajib path SPA valid (`/xxx`) atau anchor (`#xxx`); sinkronisasi isi topnav/footer menunggu G2.
- **Logo klien**: wajib ada izin pemakaian logo (legal) — seed dikosongkan dengan sengaja.
- **Objection**: `note_text` header wajib menyebut tanggal pengecekan sumber harga (lihat G5); jawaban `objection_questions` boleh rich-text/markdown sederhana.
- **Field wajib minimal**: `business_settings(business_name, contact_email)`; hero (semua kecuali gambar/warna); tiap item list (judul/pertanyaan + deskripsi/jawaban); `team_members.photo_url`; `portfolios.image_url`; `client_logos.logo_url`; `mockup_offer.image_url?` opsional; `about_timeline_items.image_url?` opsional; `seo_settings.og_image_url?` opsional; `business_settings.footer_map_url?` opsional. Daftar upload kanonis = 8 kolom di [00-status](00-status.md) No.16.

## Bukan konten homepage (tetap di dasbor)
- **Dasbor** (`/admin`): `GET /api/v1/admin/orders?limit=5` + `GET /api/v1/admin/notifications?unread=true` (tanpa endpoint agregat baru).
- **Pesanan** (`orders`, termasuk `order_type='mockup'` dengan badge beda + link `related_mockup_order_id`) → `/admin/orders`.
- **Pesan/konsultasi masuk** → `/admin/messages/contact`, `/admin/messages/consultations`.
- **Notifikasi** → `/admin/notifications`.
