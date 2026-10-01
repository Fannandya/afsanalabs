# 03 — Database (MySQL + Migrasi Laravel + Eloquent)

> Bagian dari single source of truth ([README](README.md)). Nama tabel/kolom di bawah bersifat final — migrasi Laravel maupun kode lain wajib memakainya persis agar data lama bisa dipakai ulang.

Definisi kode (target): `backend/database/migrations/*` + `backend/app/Models/*`. Dokumen acuan perilaku: `SCHEMA.md`. Akses selalu lewat Eloquent/query builder, migrasi via `php artisan migrate`, seed via `php artisan db:seed`.

## 3.1 Konsep dasar
- **Singleton (`id = 1`):** satu baris saja — `business_settings`, `hero_content`, `mockup_offer`, `seo_settings`. Admin hanya bisa GET+PATCH, tanpa create/delete (via `SingletonController`).
- **List + `sort_order` + `is_active`:** hampir semua konten homepage — admin tambah/urut/nonaktifkan tanpa hapus. Publik hanya melihat `is_active = true` terurut `sort_order` (di Eloquent via scope `scopeActive()` + `scopeOrdered()` di base model/trait).
- **Header generik:** `section_headers(section_key UNIQUE)` menampung eyebrow+heading+subtitle+intro+note untuk 9 section tetap (lihat §3.3), supaya tidak bikin tabel singleton per section.
- **ID internal tidak publik:** `orders.id` tidak pernah keluar API publik; publik hanya kenal `tracking_code` (disembunyikan via `$hidden` / API Resource yang hanya mengekspos field publik).
- **Casts Eloquent:** `boolean` untuk `is_active/show_price/is_featured/is_recommended/is_read`, `array` untuk `features/feature_bullets` (kolom JSON), `decimal:2` untuk `price/mockup_fee`, enum MySQL tetap enum di migrasi (`$table->enum(...)`).

## 3.2 Tabel auth (bawaan Laravel, pengganti tabel Better Auth)
| Tabel | Isi |
|---|---|
| `users` | akun admin (`id` auto-increment, name, email UNIQUE, `password` hash via `Hash::make`, `remember_token?`, timestamps). Tanpa kolom `role` (single-admin). Menggantikan tabel `user` + `account` Better Auth |
| `sessions` | sesi driver database (id, user_id?, ip_address?, user_agent?, payload, last_activity) — lifetime 120 menit. Menggantikan tabel `session` Better Auth |
| `password_reset_tokens` | token lupa-sandi (email PK, token, created_at) — via `Password` broker. Menggantikan tabel `verification` Better Auth |

## 3.3 Global situs — `CreateBusinessSettingsTable` dkk.
- **`business_settings`** (singleton): `business_name` (logo+footer), `footer_tagline`, `footer_copyright`, `contact_email` (tujuan email notifikasi), `phone`, `whatsapp_number` + `whatsapp_message_template` (placeholder `{{kode}} {{paket}} {{nama}}` untuk redirect pesanan paket), `address`, `description`, `payment_instructions` (**untuk mockup**, bukan paket), `footer_map_url` (gambar latar footer, opsional).
- **`nav_links`**: `label`, `target` (path SPA `/xxx` atau anchor `#xxx`), `placement ENUM(topnav,footer)` — satu tabel untuk dua penempatan supaya tidak dobel kelola, `sort_order`, `is_active`.
- **`footer_legal_links`**: `label`, `url`, `sort_order`, `is_active`.
- **`seo_settings`** (singleton): `meta_title`, `meta_description`, `meta_keywords?`, `og_image_url?` — dipasang client-side via `<svelte:head>` saat homepage dimuat, karena SPA ini statis tanpa SSR.
- **`section_headers`**: `section_key UNIQUE` (`value_props`, `process`, `objection`, `services`, `portfolio`, `pricing`, `faq`, + `about`, `team`), `eyebrow_text?`, `heading`, `subtitle?`, `intro_text?` (value_props, objection), `note_text?` (disclaimer sumber harga objection). `hero` + `mockup_offer` tidak pakai ini (field-nya beda bentuk).

## 3.4 Konten homepage — satu migrasi + satu model per tabel
| Tabel | Kolom penting | Catatan |
|---|---|---|
| `hero_content` (singleton) | eyebrow, heading, subheading, cta_label+cta_target, trust_badge_text?, image_url?, text_color? (`#RRGGBB`) | Warna untuk teks saja, bukan tombol CTA |
| `value_props` | title, description | 4 item default, skema tidak hard-limit |
| `process_steps` | step_number (1–4, `tinyInteger`), icon?, title, description | Nomor tampil eksplisit di UI |
| `objection_questions` | question, answer | Edukasi pra-keputusan |
| `reference_price_cards` | label, price_value (**teks bebas** mis. "Rp150-200rb" supaya bisa rentang), price_note? ("/tahun") | **Harga pihak ketiga (domain/hosting), bukan harga kita** — perlu diupdate berkala |
| `services` | name, description, icon? | + timestamps |
| `categories` | name, slug UNIQUE, sort_order | Dikelola admin, bukan enum (+ timestamps). Relasi: `Category::hasMany(Portfolio)` |
| `portfolios` | category_id FK→categories NULL (`nullOnDelete`), title, image_url, description?, client_name?, project_url?, is_featured (homepage), is_active | Homepage = hanya `is_featured`; `/portofolio` = semua `is_active`. Relasi: `Portfolio::belongsTo(Category)` (eager-load di home) |
| `testimonials` | client_name, content, rating 1–5 (`tinyInteger`) | Tampil di `/portofolio`, bukan homepage |
| `price_packages` | name, tagline?, price? NULL (`decimal 12,2`) + show_price, features JSON?, is_recommended (cukup satu — enforcement di FormRequest + transaksi, bukan constraint DB), cta_label, cta_action ENUM(order,contact) | Figma tidak menampilkan angka harga → `price` nullable. `contact` dipakai paket Custom |
| `mockup_offer` (singleton) | eyebrow, heading, description, feature_bullets JSON, price (wajib), cta_label, image_url? | Harga di-snapshot ke `orders.mockup_fee` saat dipesan |
| `faqs` | question, answer | Jawaban belum ada di Figma — TODO konten sebelum go-live |
| `team_members` | name, role, photo_url (wajib), twitter/facebook/linkedin_url? | Social button hanya muncul bila URL diisi |
| `client_logos` | name (jadi alt text), logo_url, link_url? | Tanpa section header (strip polos). Seed kosong — jangan isi logo brand tanpa izin (legal) |
| `about_timeline_items` | period (teks bebas "2023–2024"), title, description, image_url? (kosong → inisial judul) | Section About/Team/Logo **tidak dirender sama sekali** bila list aktif kosong |

## 3.5 Transaksional — `CreateOrdersTables`
- **`contact_messages`**: name, email, phone?, message, status ENUM(baru,dibaca,dibalas) default `baru`, `$table->timestamps()` (`created_at` untuk urut, `updated_at` berubah saat `PATCH .../:id/status`).
- **`consultations`**: sama + `business_type?`.
- **`orders`**: `tracking_code VARCHAR(20) UNIQUE` (mis. `ORD-7X9K2M`), `order_type ENUM(paket,mockup)`, `package_id FK→price_packages NULL` (wajib bila paket), `mockup_fee DECIMAL(12,2)?` (snapshot harga saat pesan mockup), `related_mockup_order_id?` (self-ref logis ke `orders.id`, tanpa FK keras — untuk potongan manual oleh admin), nama/email/phone pelanggan, `notes?`, `status ENUM(menunggu_konfirmasi, menunggu_pembayaran, dalam_pengerjaan, selesai, dibatalkan)` — `menunggu_konfirmasi` = paket (lanjut WhatsApp), `menunggu_pembayaran` = mockup (bayar di muka). Index: UNIQUE tracking_code; index package_id, related_mockup.

## 3.6 Notifikasi — `CreateNotificationTables`
- **`notification_preferences`**: `user_id FK→users UNIQUE`, 4 flag (`notify_new_order`, `notify_contact_message`, `notify_consultation`, **`notify_mockup_request`** — semuanya default true), `updated_at`.
- **`notifications`**: `user_id FK→users`, `type ENUM(pesanan_baru, pesan_kontak, konsultasi_baru, permintaan_mockup)`, `message`, `related_id?`, `is_read`, `created_at`.
- **Mapping kanonis (lihat [00-status](00-status.md) No.17):** `pesanan_baru→notify_new_order`, `permintaan_mockup→notify_mockup_request`, `pesan_kontak→notify_contact_message`, `konsultasi_baru→notify_consultation`.

## 3.7 Relasi (ER ringkas — sama, notasi Eloquent)
```
User hasMany Session | hasOne NotificationPreference | hasMany Notification
Category hasMany Portfolio | Portfolio belongsTo Category
PricePackage hasMany Order | Order belongsTo PricePackage
Order self-ref (related_mockup_order_id, logis, tanpa FK)
SectionHeader ..> ValueProp, ProcessStep, ObjectionQuestion,
                  ReferencePriceCard (logis via section_key, bukan FK)
```

## 3.8 Migrasi & seed (Artisan)
- Migrasi: `php artisan make:migration` per tabel/perubahan skema; `php artisan migrate` (pengganti `db:generate`/`db:migrate` Drizzle Kit). Foreign key ditulis eksplisit (`foreignId(...)->nullable()->constrained()->nullOnDelete()`).
- Seed (`database/seeders/`, idempoten via `firstOrCreate`): `AdminSeeder` (satu admin via `Hash::make`, dari `SEED_ADMIN_EMAIL`/`SEED_ADMIN_PASSWORD`, default `admin@lima.ai`/`ChangeMe123!` — segera diganti) + `ContentSeeder` (isi default yang sama seperti dulu: business_settings "LIMA AI", hero, nav_links, legal_links, 9 section_headers, value_props×4, process_steps×4, objection×3 + price_cards×3, services×4, categories + contoh portfolios (3 `is_featured`), price_packages×3, mockup_offer, faqs×4) + baris `notification_preferences` default-ON untuk admin. Tabel transaksional dibiarkan kosong.
- Koneksi: `DB_*` (`DB_HOST=localhost` di cPanel; dev lokal MySQL dengan kredensial sendiri).
