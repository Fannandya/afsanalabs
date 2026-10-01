# 05 — Referensi API (Ringkas)

> Bagian dari single source of truth ([README](README.md)). Kontrak di bawah **dibekukan** — URI, method, dan bentuk response tidak boleh berubah tanpa keputusan baru di [00-status](00-status.md). Implementasi: `backend/routes/api.php`, guard admin `auth:sanctum`.

Base: `/api/v1`. Sukses `{ data }`, error `{ error: { message, code?, fields? } }` (validasi → **422** ala Laravel, dulu 400 — frontend aman karena hanya membaca `error.message`; throttle → `429`).

## Publik (tanpa login, 4 POST = `throttle:10,1`)
| Method & path | Fungsi |
|---|---|
| `GET /api/v1/home` | Satu payload homepage (`HomeController@index`) |
| `GET /api/v1/portfolios?category=:slug` | Semua karya aktif (+ filter slug kategori) |
| `GET /api/v1/categories` | Daftar kategori |
| `GET /api/v1/testimonials` | Testimoni aktif |
| `POST /api/v1/contact` | Kirim pesan (`throttle:10,1`) → `201 { message }` |
| `POST /api/v1/consultations` | Kirim konsultasi (`throttle:10,1`) |
| `POST /api/v1/orders` | Buat pesanan paket → `201 { trackingCode, whatsappUrl }` (paket tak ada → `404`; paket nonaktif → `422 VALIDATION_ERROR`) |
| `POST /api/v1/mockup-requests` | Buat permintaan mockup → `201 { trackingCode, mockupFee, paymentInstructions }` (`mockup_offer` belum seed → `404`) |
| `GET /api/v1/orders/track/:trackingCode` | Lacak status publik (tanpa `id`) |

## Auth (Sanctum, pengganti Better Auth, 3 POST = `throttle:10,1`)
| Method & path | Fungsi |
|---|---|
| `GET /sanctum/csrf-cookie` | Wajib dipanggil frontend (dengan credentials) sebelum login/request admin |
| `POST /api/v1/admin/login` | Login email+password (`Auth::attempt`) |
| `POST /api/v1/admin/logout` | Logout (revoke sesi) |
| `POST /api/v1/admin/forgot-password` | Kirim link reset (`Password::sendResetLink`) |
| `POST /api/v1/admin/reset-password` | Reset sandi (`Password::reset`) |

## Admin (wajib `auth:sanctum`)
CRUD list (`GET /`, `GET /:id`, `POST /`, `PATCH /:id`, `DELETE /:id → 204`), via base `CrudController`. Paginasi list transaksional: `?page&limit` (default 20, maks 100) → `{ data, page, limit }`:
`/api/v1/admin/value-props`, `/process-steps`, `/objection-questions`, `/reference-price-cards`, `/services`, `/categories`, `/portfolios`, `/testimonials`, `/packages` (beku, tabel `price_packages`), `/team-members`, `/client-logos`, `/about-timeline` (beku, tabel `about_timeline_items`), `/faqs`, `/nav-links`, `/footer-legal-links`

Singleton (GET + PATCH saja, `id=1`), via base `SingletonController`:
`/api/v1/admin/hero`, `/mockup-offer`, `/business-settings`, `/seo-settings`

Header section (PATCH by key, bukan id):
- `GET /api/v1/admin/section-headers` → semua header
- `PATCH /api/v1/admin/section-headers/:sectionKey` → update satu section; 404 bila key tidak ada

Pesan masuk:
- `GET /api/v1/admin/contact-messages?page=&limit=` → list paginasi (default 20, maks 100)
- `PATCH /api/v1/admin/contact-messages/:id/status` → `{ status: in:baru,dibaca,dibalas }` → `{ ok: true }`
- `GET /api/v1/admin/consultations?page=&limit=` dan `PATCH /api/v1/admin/consultations/:id/status` — pola sama

Operasional:
| Method & path | Fungsi |
|---|---|
| `GET /api/v1/admin/orders?page=&limit=` | List pesanan (default 20, maks 100, terbaru dulu) |
| `GET /api/v1/admin/orders/:id` | Detail internal |
| `PATCH /api/v1/admin/orders/:id/status` | Update status (`in:menunggu_konfirmasi,menunggu_pembayaran,dalam_pengerjaan,selesai,dibatalkan`), balas row terbaru |
| `PATCH /api/v1/admin/password` | Ganti sandi login (`current_password`, `password|min:8|confirmed`) |
| `GET /api/v1/admin/notifications?unread=true` | Notifikasi milik admin login (filter belum dibaca opsional, limit 100) |
| `PATCH /api/v1/admin/notifications/:id/read` | Tandai dibaca → `{ ok: true }` |
| `GET /api/v1/admin/notifications/preferences` | Preferensi email admin (atau `null`) |
| `PATCH /api/v1/admin/notifications/preferences` | Update 4 flag email |
| `POST /api/v1/admin/uploads?folder=` | Upload gambar → `201 { url }` (JPG/PNG/WEBP, maks 2MB, nama UUID, URL `/storage/...`) |

## Lainnya
- `GET /health` → `{ status: "ok" }` (`HealthController@index`, tanpa `auth`/`throttle`, untuk healthcheck kontainer `app`)
- `GET /storage/*` → file statis gambar (pengganti `/uploads/*`; nilai URL di DB ikut migrasi)
