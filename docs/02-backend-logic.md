# 02 — Logic Backend (Laravel + Sanctum + Eloquent)

> Bagian dari single source of truth ([README](README.md)). URI, status code, dan bentuk response mengikuti kontrak yang dibekukan di [05](05-api-reference.md). Referensi path adalah struktur Laravel target (`backend/routes/api.php`, `backend/app/...`).

## 2.1 Wiring utama — `routes/api.php` + `bootstrap/app.php`
- Prefix global `api/v1` (seperti dulu `/api/v1/*`). Route publik didaftar polos; route admin dibungkus `middleware('auth:sanctum')` — tidak ada lagi guard manual per-router karena middleware grup menanganinya di satu tempat (dulu `requireAuth` ditempel per router).
- CORS: `config/cors.php` (`allowed_origins = [FRONTEND_URL]`, `supports_credentials = true`) — pengganti `cors({ origin, credentials })`.
- Security headers: middleware Laravel default (+ `TrustProxies` karena di belakang nginx/Cloudflare, pengganti `trust proxy = 1` agar IP asli terbaca rate-limiter).
- CSRF/XSRF: Sanctum stateful — frontend wajib `GET /sanctum/csrf-cookie` dulu; request state-changing menyertakan cookie `XSRF-TOKEN` (axios/fetch dengan `credentials: include` menanganinya otomatis).
- Statis: gambar di `storage/app/public` → symlink `public/storage`, diserve nginx langsung di `/storage/*`. Folder karantina `.trash` dikecualikan dari symlink/web-root (dulu `dotfiles: ignore`).
- Exception terpusat: `bootstrap/app.php` → `withExceptions()` memetakan `ValidationException` → `422 { error: { message: "Data tidak valid.", code: "VALIDATION_ERROR", fields } }` (dulu 400 via Zod — Laravel konvensional 422; frontend hanya membaca `error.message` jadi aman), `ModelNotFoundException` → 404, selain itu 500 generik + log. Tidak ada lagi `asyncHandler` — exception Laravel otomatis ditangkap handler.

## 2.2 Homepage gabungan — `Api/Public/HomeController.php@index`
`GET /api/v1/home` tetap **satu request untuk ~9 section**. Implementasi Eloquent ekuivalen 19 query lama:
- Singleton: `BusinessSetting::find(1)`, `HeroContent::find(1)`, `MockupOffer::find(1)`, `SeoSetting::find(1)`.
- List aktif terurut: `ValueProp::active()->ordered()->get()` (scope `active()` = `where('is_active', true)`, `ordered()` = `orderBy('sort_order')`) — pola sama untuk `ProcessStep`, `ObjectionQuestion`, `ReferencePriceCard`, `Service`, `PricePackage`, `Faq`, `TeamMember`, `ClientLogo`, `AboutTimelineItem`.
- `NavLink::active()->ordered()->get()` lalu partisi `topnav`/`footer` by `placement` (collection `partition`/`groupBy`); `FooterLegalLink` sama.
- `SectionHeader::all()->keyBy('section_key')` (pengganti `headerMap`).
- Featured: `Portfolio::with('category')->where('is_featured', true)->ordered()->get()` (eager-load `category`, pengganti `categoryMap` manual).
- Bentuk payload JSON identik dengan versi Express supaya komponen homepage tidak berubah.

## 2.3 Katalog publik — `Api/Public/PortfolioController.php`
- `GET /api/v1/portfolios?category=:slug` — cari `Category::where('slug', $slug)->first()`; tidak ketemu → `{ data: [] }`; selain itu `Portfolio::active()->when($categoryId)->ordered()->get()`.
- `GET /api/v1/categories`, `GET /api/v1/testimonials` — list terurut seperti §2.2.

## 2.4 Form publik — `Api/Public/FormController.php` + `OrderTrackController.php`
`throttle:10,1` (= 10 request/menit per IP, lebih → `429`) untuk 4 endpoint di bawah + 3 endpoint auth (`POST /api/v1/admin/login`, `/forgot-password`, `/reset-password`, lihat §2.8). Semua ber-body memakai FormRequest (`lib/validation.ts` → `app/Http/Requests/*`).

| Endpoint | FormRequest | Logic (sama, gaya Eloquent) |
|---|---|---|
| `POST /api/v1/contact` | `StoreContactMessageRequest` (nama, email valid, pesan) | `ContactMessage::create([... 'status' => 'baru'])` → `NotifyService::send('pesan_kontak', ...)` → `201 { message }` |
| `POST /api/v1/consultations` | `StoreConsultationRequest` (+`business_type` opsional) | `Consultation::create([...])` → `NotifyService::send('konsultasi_baru', ...)` |
| `POST /api/v1/orders` (paket) | `StoreOrderRequest` (`package_id` exists, nama, email, phone wajib, `related_mockup_order_id` opsional) | cek paket: 404 bila `id` tak ada, 422 `VALIDATION_ERROR` bila ada tapi `is_active=false` → `TrackingCodeService::generate()` → `Order::create(['order_type' => 'paket', 'status' => 'menunggu_konfirmasi', ...])` → notify `pesanan_baru`. Baca `BusinessSetting::find(1)` → bila `whatsapp_number` ada, rakit `whatsappUrl` via `Whatsapp::fillTemplate($template ?? default, [kode, paket, nama])`; bila tidak ada → `null`. `201 { trackingCode, whatsappUrl }` |
| `POST /api/v1/mockup-requests` | `StoreMockupRequestRequest` (data diri saja) | `MockupOffer::findOrFail(1)` (404 bila row `id=1` belum diseed) → tracking baru → `Order::create(['order_type' => 'mockup', 'mockup_fee' => $offer->price, 'status' => 'menunggu_pembayaran'])` → notify `permintaan_mockup`. `201 { trackingCode, mockupFee, paymentInstructions }` (dari `business_settings.payment_instructions`) |
| `GET /api/v1/orders/track/{trackingCode}` | — (tanpa auth) | `Order::where('tracking_code', $code)->firstOrFail()` → hanya `tracking_code, order_type, status, created_at, updated_at` — `id` internal tidak pernah diekspos |

## 2.5 Admin CMS — 15 CRUD + 4 singleton
Pengganti `routes/admin/content.ts` + `crud-factory.ts`: satu **base controller generik** + FormRequest per resource (bukan 15 controller duplikat).
- **15 CRUD list** (`Concerns/CrudController.php` di-extend tiap resource): `value-props`, `process-steps`, `objection-questions`, `reference-price-cards`, `services`, `categories`, `portfolios`, `testimonials`, `packages`, `team-members`, `client-logos`, `about-timeline`, `faqs`, `nav-links`, `footer-legal-links`.
- **4 singleton** (`Concerns/SingletonController.php`, `show`+`update` saja, `id=1`): `hero`, `mockup-offer`, `business-settings`, `seo-settings` (+ `SectionHeaderController` terpisah, lihat §2.7b).
- Tiap resource punya `StoreXRequest`/`UpdateXRequest` (POST pakai rules penuh, PATCH pakai `sometimes` — ekuivalen `.partial()` Zod).

## 2.6 Base CRUD controller — `Concerns/CrudController.php` + `SingletonController.php`
- `index()`: `$model::ordered()->get()` (atau tanpa order bila modelnya tak punya `sort_order`) → `{ data }`.
- `show($id)`: `findOrFail` → 404 otomatis → `{ data }`.
- `store(Request)`: `$request->validated()` + tambah timestamps otomatis Eloquent (`$timestamps = true`) → `201` model baru.
- `update($id)`: baca URL file lama → `$model->update($validated)` → hapus file lama yang diganti via `Storage::disk('public')->delete()` → `{ data }`.
- `destroy($id)`: hapus row → hapus file di `$imageFields` dari disk → `204`.
- `SingletonController`: `show()` (row `id=1` atau `null`), `update()` (+ hapus foto lama).
- `$imageFields` kanonis (8 kolom, sama persis dengan [00-status](00-status.md) No.16): `hero_content.image_url`, `portfolios.image_url`, `team_members.photo_url`, `client_logos.logo_url`, `about_timeline_items.image_url`, `mockup_offer.image_url`, `seo_settings.og_image_url`, `business_settings.footer_map_url`. Tiap controller menyalin subset miliknya dan **wajib disalin ke `CleanupOrphanedUploads`** supaya file yang masih dipakai tidak dianggap yatim.

## 2.7 Admin pesanan — `Api/Admin/OrderController.php`
Grup `auth:sanctum`. `index()` paginasi (`?page` + `?limit`, default 20, maks 100; `$limit = min($request->integer('limit', 20), 100)` lalu `Model::query()->latest('created_at')->paginate($limit)`, kemudian map ke respons `{ data, page, limit }` agar bentuknya sama seperti dulu). `show($id)` detail internal. `updateStatus($id, UpdateOrderStatusRequest)` validasi enum 5 status (`menunggu_konfirmasi`, `menunggu_pembayaran`, `dalam_pengerjaan`, `selesai`, `dibatalkan`) lalu `touch updated_at`, balas row terbaru.

## 2.7b Admin pesan, notifikasi, section-header
- Pesan (`MessageController.php`, prefix `/api/v1/admin`): `GET /contact-messages` + `GET /consultations` (paginasi `?page&limit` sama seperti §2.7); `PATCH /contact-messages/{id}/status` + `PATCH /consultations/{id}/status` — validasi ringan (`in:baru,dibaca,dibalas`, perbaikan dari versi Express yang memakai body mentah) dan membalas `{ ok: true }`, bukan row.
- Notifikasi (`NotificationController.php`, prefix `/api/v1/admin/notifications`, `auth:sanctum` milik user login): `GET /` (`?unread=true` opsional, limit 100, terbaru dulu); `PATCH /{id}/read` → `{ ok: true }`; `GET/PATCH /preferences` (4 flag email).
- Section header (`SectionHeaderController.php`): `GET /` semua header; `PATCH /{sectionKey}` via FormRequest + 404 bila key tidak ada. Pola by-key, bukan by-id.

## 2.8 Auth — Sanctum (`config/sanctum.php`, `config/session.php`, `config/auth.php`)
- Pengganti Better Auth + `drizzleAdapter`: tabel `users` bawaan Laravel (satu admin, tanpa kolom `role`) + sesi driver `database` + `password_reset_tokens` (pengganti tabel `session`/`verification` Better Auth).
- Login email+password (`Auth::attempt`, hash via `Hash::make` — tanpa hashing manual di controller), `min:8` di FormRequest. Route auth (`login`, `forgot-password`, `reset-password`) memakai `throttle:10,1`.
- Sesi **idle-timeout 120 menit**: `SESSION_LIFETIME=120`, `expire_on_close=false`. Diputus: sliding Laravel (idle mutlak 2 jam), tanpa middleware absolut-keras `login_at`.
- Guard: `auth:sanctum` → 401 `{ error: { message: "Belum masuk..." } }` bila tidak ada (via `AuthenticationException` di exception handler) — frontend tetap redirect ke `/admin/login` seperti sekarang.
- Lupa sandi: `Password::sendResetLink` + `Password::reset` (pengganti endpoint `verification` Better Auth).
- Ganti sandi (login): `PATCH /api/v1/admin/password` (`UpdatePasswordRequest`: `current_password` + `password|min:8|confirmed`, verifikasi via `Hash::check`, simpan via `Hash::make`).

## 2.9 Validasi — `app/Http/Requests/*.php`
Pengganti `lib/validation.ts` (Zod → FormRequest `rules()` + `messages()` Bahasa Indonesia). Contoh pemetaan: `hero.text_color` → `nullable|regex:/^#[0-9a-fA-F]{6}$/` + `prepareForValidation` yang mengubah `""` jadi `null` (form admin mengirim string kosong saat warna dikosongkan); `price` paket → `nullable|numeric`; `image_url` portofolio → `required|string|max:500`; `rating` → `integer|min:1|max:5`; `cta_action` → `in:order,contact`; status order → `in:menunggu_konfirmasi,menunggu_pembayaran,dalam_pengerjaan,selesai,dibatalkan`. PATCH memakai `sometimes` (ekuivalen `.partial()`). `is_recommended`: bila request `true`, dalam transaksi DB set semua `price_packages` lain `is_recommended=false` dulu (`Store/UpdatePackageRequest`).

## 2.10 Notifikasi — `app/Services/NotifyService.php` + `app/Mail/AdminNotificationMail.php`
`NotifyService::send($type, $message, $relatedId)`: ambil admin pertama (single-admin) → **selalu** `Notification::create([... 'is_read' => false])` → cek `NotificationPreference` sesuai mapping ([00-status](00-status.md) No.17: `pesanan_baru→notify_new_order`, `permintaan_mockup→notify_mockup_request`, `pesan_kontak→notify_contact_message`, `konsultasi_baru→notify_consultation`; default ON bila baris belum ada) → bila ON, kirim `Mail::to($businessSetting->contact_email ?? $admin->email)->send(new AdminNotificationMail($subject, $message))` dalam `try/catch`.
- Email **best-effort, tidak pernah memblokir request**: bila `MAIL_HOST/USERNAME/PASSWORD` tidak diset → `Log::info(...)`, request tetap sukses (in-app `notifications` sumber kebenaran). Gagal kirim → `Log::error`, bukan 500. Konfig: `MAIL_MAILER/HOST/PORT/USERNAME/PASSWORD/FROM_ADDRESS` (lihat `.env.example` Laravel).

## 2.11 Tracking code — `app/Services/TrackingCodeService.php`
`ORD-` + 6 karakter alfabet tanpa `0/O/1/I` (`Str::random` dengan alfabet custom, bukan `Str::random` default yang mengandung karakter ambigu) → cek `Order::where('tracking_code', $code)->exists()` → retry maks 5x, lalu `abort(500)` bila gagal (kasus praktis tidak terjadi).

## 2.12 Upload — `Api/Admin/UploadController.php` + rule magic-bytes + `Storage`
Grup `auth:sanctum`. Validasi: `file|max:2048` (2MB → pesan "Ukuran file terlalu besar (maks 2MB).") + `mimes:jpg,png,webp` + **custom rule `ImageMagicBytes`** yang membaca header buffer (JPEG `FF D8 FF`, PNG 8-byte signature, WEBP `RIFF....WEBP`) — ekstensi tidak dipercaya. Folder dari `?folder=` (whitelist `^[a-z-]+$`, default `misc`, tolak `..`/traversal) → `Storage::disk('public')->putFileAs($folder, $file, Str::uuid().ext)` → `201 { url: /storage/<folder>/<file> }` (perhatikan: path publik berubah dari `/uploads/...` menjadi `/storage/...`; komponen upload admin (`ImageUploadField.svelte`) + kolom DB yang menyimpan URL harus ikut migrasi nilainya).
- Hapus file lama: `Storage::disk('public')->delete($relativePath)` hanya bila path di dalam disk (guard traversal), missing file diabaikan — cleanup tidak pernah menggagalkan operasi DB.
- Artisan `php artisan uploads:cleanup` (`app/Console/Commands/CleanupOrphanedUploads.php`): kumpulkan URL dari 8 kolom kanonis ([00-status](00-status.md) No.16) → default **dry-run**; `--apply` **pindahkan** (bukan hapus) ke `storage/app/public/.trash/<timestamp>/...`; file lebih baru dari `--min-age-hours=` (default 24) dilewati — melindungi file yang baru diupload tapi form adminnya belum disimpan.

## 2.13 Middleware & fondasi lain
- `throttle:10,1` (= **10 request/menit per IP**, lebih → `429`) untuk 4 form publik (§2.4) + 3 endpoint auth (§2.8) (pengganti `express-rate-limit`).
- Exception handler (`bootstrap/app.php`): validasi → 422 + `fields`; model hilang → 404; auth → 401; throttle → 429; selain itu log + 500 generik. (Perbedaan disengaja: 422 bukan 400 — frontend aman karena hanya membaca `error.message`.)
- `App\Support\Whatsapp`: `normalize()` (`08xx` → `62xx`, buang non-digit), `fillTemplate()` (`{{kode}} {{paket}} {{nama}}`), `url()` (`https://wa.me/...`). Template default sama: `"Halo, saya {{nama}} ingin memesan paket {{paket}} dengan kode booking {{kode}}."`.
- Koneksi DB (`config/database.php` mysql): dari `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD`. Serve: `php artisan serve` (dev) / PHP-FPM + nginx (prod).

## 2.14 Health & ganti sandi
- `GET /health` → `HealthController@index` (route Laravel polos tanpa `auth`/`throttle`, balas `{ status: "ok" }`) untuk healthcheck kontainer `app`.
- `PATCH /api/v1/admin/password` → `PasswordController@update` (`auth:sanctum`, lihat §2.8).
