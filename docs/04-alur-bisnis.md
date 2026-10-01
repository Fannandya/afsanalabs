# 04 — Alur Bisnis End-to-End

> Bagian dari single source of truth ([README](README.md)). Alur di bawah bersifat final; URI endpoint mengacu kontrak [05](05-api-reference.md).

## 4.1 Pengunjung: homepage → portofolio
1. Buka `/` → frontend `GET /api/v1/home` sekali (`HomeController@index`), render blok berurutan (TopNavBar → Hero → ValueProps → Proses → Objection → Layanan → Portofolio preview → Pricing → Mockup → FAQ → Footer; plus About/Tim/Logo Klien bila ada isi — ketiganya auto-sembunyi kalau kosong). Lihat `ARCHITECTURE.md §4`.
2. Klik **Lihat Lebih Banyak** di preview portofolio → `goto('/portofolio')` SvelteKit (tanpa reload) → halaman fetch `GET /api/v1/portfolios` + `/categories` + `/testimonials` dengan filter kategori.

## 4.2 Pesan paket (tanpa bayar di muka)
1. Klik "Pilih Paket" (`cta_action=order`) → `/pesan?package=:id` → isi nama/email/phone/catatan (+ `relatedMockupOrderId` bila lanjutan mockup).
2. `POST /api/v1/orders` (`FormController@storeOrder`, `throttle:10,1`) → cek paket aktif → `tracking_code` baru (`TrackingCodeService`) → `Order::create(paket, menunggu_konfirmasi)` → `NotifyService` ke admin → balas `{ trackingCode, whatsappUrl }`.
3. Frontend redirect otomatis ke `whatsappUrl` (`wa.me` + pesan terisi kode/paket/nama via `App\Support\Whatsapp`) + tetap tampilkan tracking code + tombol manual (fallback bila redirect diblokir).
4. Diskusi harga/scope/bayar lanjut di WhatsApp (di luar sistem). Cek status kapan saja: `GET /api/v1/orders/track/:trackingCode`.
5. Admin update manual: `PATCH /api/v1/admin/orders/:id/status`. Paket: `menunggu_konfirmasi → dalam_pengerjaan → selesai`, atau `dibatalkan`. Mockup: `menunggu_pembayaran → dalam_pengerjaan → selesai`, atau `dibatalkan`.

## 4.3 Minta mockup (berbayar di muka)
1. Klik CTA mockup → `/mockup` → isi data diri.
2. `POST /api/v1/mockup-requests` → snapshot `mockup_offer.price` → `Order::create(mockup, menunggu_pembayaran, mockup_fee)` → balas `{ trackingCode, mockupFee, paymentInstructions }` (instruksi dari `business_settings.payment_instructions`).
3. Pelanggan transfer manual. Bila lanjut ke paket penuh: buat pesanan paket baru dengan `related_mockup_order_id` → admin lihat relasi di dasbor dan **potong manual** `mockup_fee` saat menagih (v1 tidak hitung otomatis).

## 4.4 Konsultasi / kontak
- `/konsultasi` → `POST /api/v1/consultations` (paket Custom `cta_action=contact` juga ke sini). Form kontak → `POST /api/v1/contact`. Keduanya `throttle:10,1` + `NotifyService` (`konsultasi_baru` / `pesan_kontak`). Admin baca di `GET /api/v1/admin/contact-messages` / `/consultations` + ubah status via `PATCH .../:id/status` (balas `{ ok: true }`) di dasbor.

## 4.5 Lacak pesanan (publik)
`/lacak` → input tracking code → `GET /api/v1/orders/track/:trackingCode` → tampil `orderType + status + tanggal`. Tidak butuh login, tidak mengekspos `id`.

## 4.6 Admin CMS
1. Login `/admin/login` (Sanctum: ambil `/sanctum/csrf-cookie` dulu, lalu login email+password, sesi idle-timeout 120 menit) → Dasbor (`/admin` = `GET /api/v1/admin/orders?limit=5` + `GET /api/v1/admin/notifications?unread=true`, tanpa endpoint agregat baru).
2. Kelola konten: tiap tabel = pola sama (list → Tambah/Ubah → ConfirmDialog sebelum hapus → Toast). `sort_order` via drag-and-drop/angka. Toggle `is_active` untuk sembunyikan tanpa hapus; toggle `is_featured` untuk portofolio homepage (sarankan 3–4 sesuai grid 3-kolom).
3. Kelola pesanan: bedakan badge mockup vs paket, lihat `related_mockup_order_id`, verifikasi bayar manual, update status.
4. Upload gambar: via `POST /api/v1/admin/uploads` (Laravel Storage, `max:2048` + magic-bytes, nama UUID, URL `/storage/...`) untuk 8 kolom kanonis ([00-status](00-status.md) No.16: `portfolios.image_url`, `hero_content.image_url`, `mockup_offer.image_url`, `team_members.photo_url`, `client_logos.logo_url`, `about_timeline_items.image_url`, `seo_settings.og_image_url`, `business_settings.footer_map_url`). File lama auto-hapus saat diganti/dihapus.
5. Pengaturan: Info Bisnis+Navigasi+Legal (satu halaman `/admin/settings/business`), SEO meta, kata sandi (`PATCH /api/v1/admin/password`), preferensi notifikasi. Logout setelah selesai (revoke sesi Sanctum).

## 4.7 Notifikasi ke admin
Setiap insert publik (order/mockup/kontak/konsultasi) memanggil `NotifyService::send()` → selalu tulis baris `notifications` (in-app) → kirim `AdminNotificationMail` hanya bila preferensi tipe itu ON. Tujuan email: `business_settings.contact_email`, fallback email admin. Tanpa `MAIL_*` terkonfigurasi: hanya log, request tetap sukses.
