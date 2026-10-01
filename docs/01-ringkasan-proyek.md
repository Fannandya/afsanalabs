# 01 — Ringkasan Proyek: Tentang Apa?

> Bagian dari single source of truth ([README](README.md)). Target backend **Laravel**, frontend **SvelteKit SPA statis**; perilaku produk dan desain UI tidak berubah dari versi lama.

## Satu kalimat
Website pemasaran **SPA + CMS** untuk jasa pembuatan website: pengunjung melihat penawaran dan memesan, pemilik jasa mengelola **seluruh isi homepage** tanpa edit kode.

## Untuk siapa
- **Pengunjung / calon pelanggan:** baca hero, value props, proses 4 langkah, objection-handling, layanan, preview portofolio, paket harga, tawaran mockup, FAQ → konsultasi / pesan paket / minta mockup / lacak pesanan.
- **Admin (satu akun saja, tidak ada role):** login di `/admin/login`, kelola tiap section homepage, portofolio, testimoni, pesanan, pesan masuk, pengaturan bisnis. Keputusan single-admin tetap berlaku.

## Acuan desain
Konten + layout homepage mengikuti desain Figma "jasa website" (brand referensi **LIMA AI**, bisa diganti admin via `business_settings.business_name`). Palet warna yang dipakai adalah "Vivid Azure" (lihat `DESIGN.md`), bukan krem-hijau Figma.

## Fitur per fase (dari `PRD.md`)
- **Fase 1 — Homepage SPA (`/`):** 9 section: Hero, "Apa yang Kami Maksud dengan Website Profesional" (4 poin), Proses Kerja (4 langkah), Pra-Keputusan (3 pertanyaan + 3 kartu referensi harga domain/hosting), Layanan (4), Portofolio preview (3 featured + tombol Lihat Lebih Banyak), Paket & Harga (Startup/Business/Custom), Tawaran Mockup berbayar, FAQ accordion. Plus TopNavBar + Footer. Tambahan belakangan: Tentang Kami (timeline), Tim Kami, Logo Klien — ketiganya **auto-sembunyi kalau list kosong**.
- **Fase 2 — Publik lanjutan:** `/portofolio` (galeri lengkap + filter kategori + testimoni), `/konsultasi`, `/pesan`, `/mockup`, `/lacak` (cek via tracking code). Catatan: `ARCHITECTURE.md §2` menyebut `OrderConfirmation` (`/pesan/konfirmasi`) dan `OrderSummary`, tetapi di frontend aktual yang ada adalah form pemesanan di `/pesan` — tidak ada route `/pesan/konfirmasi` (dulu `OrderForm.tsx`, target baru `src/routes/pesan/+page.svelte`).
- **Fase 3 — Admin:** satu halaman per section CMS + Kelola Pesanan (bedakan mockup vs paket) + Pesan/Konsultasi masuk + Login/Logout/Lupa sandi/Ganti sandi (`PATCH /api/v1/admin/password`).
- **Fase 4 — Pengaturan:** Info Bisnis + Navigasi + Legal (satu halaman `/admin/settings/business` kelola 3 tabel: `business_settings`, `nav_links`, `footer_legal_links`) + SEO, Ganti Kata Sandi, Preferensi Notifikasi (termasuk tipe `permintaan_mockup`).

## Tech stack (target Laravel + Svelte)
| Lapisan | Teknologi |
|---|---|
| Frontend | SvelteKit (Svelte 5 runes) + `adapter-static`, SPA murni (CSR, fallback `index.html`), build file statis. Satu tambahan wajib: ambil CSRF cookie Sanctum (`GET /sanctum/csrf-cookie`) sebelum request admin |
| Backend | Laravel (PHP), REST API `/api/v1/*`, validasi FormRequest, `throttle` rate-limit, Storage disk publik |
| Database | Tetap MySQL 8 — via migrasi Laravel + Eloquent ORM (pengganti Drizzle Kit/Drizzle). Skema/tabel tidak berubah |
| Auth | Laravel Sanctum, SPA cookie-based (pengganti Better Auth): login email+password (min 8), sesi idle-timeout 120 menit (sliding Laravel), `auth:sanctum` untuk `/api/v1/admin/*`. Tanpa hashing manual di controller (pakai `Hash::make`/`Auth::attempt`) |
| Upload | Laravel Storage disk `public` (`storage/app/public`, symlink `public/storage` diserve web server); validasi `max:2048` + `mimes:jpg,png,webp` + rule magic-bytes; nama file UUID |
| Email | Laravel Mail via SMTP (`MAIL_*`); best-effort — tanpa mailer hanya log, request tetap sukses |
| Deploy | Shared hosting cPanel — backend di `~/backend` (docroot `~/backend/public` berisi juga hasil build SvelteKit), MySQL via wizard, TLS AutoSSL. Detail di [06-deployment.md](06-deployment.md) |
| Konfig backend | Kanonis ikut tabel [06-deployment.md](06-deployment.md) §6.4: `APP_URL` + `FRONTEND_URL` + `SANCTUM_STATEFUL_DOMAINS`, `DB_*`, `SESSION_LIFETIME=120`, `SEED_ADMIN_EMAIL/PASSWORD` (default `admin@lima.ai`/`ChangeMe123!`), `MAIL_*`. `PUBLIC_APP_URL` / `DATABASE_URL` / `BETTER_AUTH_*` tidak dipakai lagi (legacy Express) |

## Frontend SvelteKit (menggantikan SolidJS, desain UI tidak berubah)
Routing file-based di `frontend/src/routes/` (pengganti `routes.tsx` + `lazy`): publik `+page.svelte` di `/`, `/portofolio`, `/konsultasi`, `/pesan`, `/mockup`, `/lacak`; admin di `/admin/login`, `/admin`, `/admin/content/*` (hero, section-headers, value-props, process-steps, objection, services, portfolio, about-timeline, team, clients, pricing, mockup-offer, faq), `/admin/settings/*` (business, seo, testimonials), `/admin/orders`, `/admin/notifications`, `/admin/messages/contact`, `/admin/messages/consultations` — halaman admin memakai layout bersama (`+layout.svelte`, pengganti `AdminLayout`). Klien API (`src/lib/api.ts`, logika sama seperti dulu): base `/api/v1`, `credentials: "include"`, error dibaca dari `body.error.message`, `204` → `undefined`, `401` di area `/admin` (di luar halaman login) → `goto('/admin/login')` dari `$app/navigation` (menangani sesi idle-timeout 120 menit yang kedaluwarsa di tengah pemakaian); upload via `FormData` tanpa header JSON. **Baru:** sebelum login panggil `GET /sanctum/csrf-cookie` (dengan credentials) agar cookie XSRF Sanctum terpasang. State reaktif memakai runes Svelte 5 (`$state`, `$derived`, `$effect`); fetch CMS di `onMount`/`$effect`; list/kondisional dengan `{#each}`/`{#if}` (pengganti `<For>`/`<Show>`); form dengan `bind:value`. Komponen UI (`Toast`, `ConfirmDialog`, `ImageUploadField`, dsb.) ditulis ulang sebagai `.svelte` dengan `props`/`events` Svelte — tampilan dan perilaku sama, hanya sintaks yang berubah. Output `adapter-static` + `ssr = false` (SPA murni, fallback `index.html`) sehingga pola serve file statis tetap jalan; meta SEO homepage tetap dipasang client-side via `<svelte:head>` (pengganti `document.title` manual).

## Keputusan kunci (tetap, kecuali no. 7)
1. SPA — navigasi antar "halaman" tidak reload penuh; pola **preview + Lihat Lebih Banyak** untuk konten yang bisa tumbuh (portofolio).
2. **Mockup berbayar adalah produk terpisah** (`orders.order_type = 'mockup'`), bukan bagian paket; biaya mockup dipotong manual bila lanjut ke paket penuh.
3. Pembayaran **manual transfer** + verifikasi admin (tanpa payment gateway). Khususnya: pesanan **paket = tanpa bayar di muka** (lanjut diskusi WhatsApp), pesanan **mockup = bayar di muka** (dapat instruksi transfer).
4. Storage gambar = disk lokal hosting, bukan S3.
5. Kategori portofolio = tabel `categories`, bukan enum hardcode.
6. Endpoint publik pesanan **wajib pakai `tracking_code`** (`ORD-XXXXXX`), bukan `id` internal.
7. Auth via **Laravel Sanctum** (menggantikan Better Auth); sesi cookie httpOnly, idle-timeout 120 menit (sliding Laravel).

## Struktur folder (target Laravel + Svelte)
```
frontend/src/            # SvelteKit (menggantikan struktur SolidJS)
  routes/                file-based routing, satu folder per URL:
    +page.svelte         # / (homepage, merangkai section)
    +layout.svelte       # layout publik (TopNavBar + Footer)
    portofolio/+page.svelte, konsultasi/+page.svelte,
    pesan/+page.svelte, mockup/+page.svelte, lacak/+page.svelte
    admin/login/+page.svelte
    admin/+layout.svelte # layout admin (pengganti AdminLayout)
    admin/+page.svelte   # Dashboard
    admin/content/hero/+page.svelte, ... (satu folder per tabel CMS)
    admin/settings/business/+page.svelte, seo/+page.svelte,
      testimonials/+page.svelte
    admin/orders/+page.svelte, admin/notifications/+page.svelte,
    admin/messages/contact/+page.svelte,
    admin/messages/consultations/+page.svelte
  lib/
    api.ts               # klien fetch (base /api/v1, 401 → goto login)
    components/home/     satu .svelte per section homepage
      (Hero, ValueProps, Process, Objection, Services,
       PortfolioPreview, Pricing, MockupOffer, Faq, ...)
    components/ui/       Button, Card, Modal, Toast, Accordion, Badge, ...
    components/admin/    ResourceCrud, SingletonForm, ResourceForm,
                         ImageUploadField, ConfirmDialog (.svelte)
  svelte.config.js       # adapter-static, SPA fallback
```

> Catatan: `/admin/settings/business` mengelola 3 tabel sekaligus (`business_settings`, `nav_links`, `footer_legal_links`). Tidak ada route settings/nav terpisah.

backend/                 # Laravel (menggantikan backend/src Express)
  routes/api.php         wiring /api/v1/* (publik + auth:sanctum untuk admin)
  app/Http/Controllers/Api/Public/
    HomeController.php, PortfolioController.php, FormController.php,
    OrderTrackController.php
  app/Http/Controllers/Api/Admin/
    Concerns/CrudController.php      # base generik (pengganti crud-factory)
    Concerns/SingletonController.php # base id=1 (read+update)
    ValuePropController.php, ProcessStepController.php, ... (15 resource)
    HeroController.php, MockupOfferController.php,
    BusinessSettingController.php, SeoSettingController.php,
    SectionHeaderController.php, OrderController.php,
    MessageController.php, NotificationController.php, UploadController.php
  app/Http/Requests/     FormRequest per resource + StoreOrderRequest,
                         StoreMockupRequestRequest, UpdateOrderStatusRequest, ...
  app/Models/            Eloquent per tabel (BusinessSetting, NavLink, HeroContent,
                         ValueProp, ..., Portfolio, Category, PricePackage,
                         MockupOffer, Faq, TeamMember, ClientLogo, AboutTimelineItem,
                         ContactMessage, Consultation, Order, Notification, ...)
  app/Services/          NotifyService.php, TrackingCodeService.php,
                         Whatsapp.php, UploadCleanup.php
  app/Mail/              AdminNotificationMail.php
  app/Console/Commands/  CleanupOrphanedUploads.php  # php artisan uploads:cleanup
  database/migrations/   satu migrasi per tabel/perubahan skema
  database/seeders/      AdminSeeder + ContentSeeder (idempoten)
  config/                cors.php, session.php (lifetime 120), sanctum.php, mail.php,
                         filesystems.php (disk public)
```
