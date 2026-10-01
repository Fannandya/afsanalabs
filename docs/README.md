# Docs — Single Source of Truth Pengembangan Aplikasi

> Folder ini adalah **acuan resmi** pengembangan aplikasi. Bila bertentangan dengan dokumen root lama (`PRD.md`, `ARCHITECTURE.md`, `SCHEMA.md`, `CMS.md`, `RULES.md`, `DESIGN.md`), folder `features/`, atau kode lama — **yang berlaku adalah `docs/`**. Dokumen lama dipakai hanya sebagai referensi sejarah (alasan keputusan) dan kriteria acceptance `features/` ("Selesai bila").

## Peta dokumen

| File | Isi | Status |
|---|---|---|
| [00-status.md](00-status.md) | Keputusan terkunci, status spec-vs-kode, gap terbuka, testing minimum | Normatif |
| [00-konvensi.md](00-konvensi.md) | Aturan pengembangan (penamaan, API, validasi, keamanan, error, git) | Normatif |
| [01-ringkasan-proyek.md](01-ringkasan-proyek.md) | Gambaran proyek, fitur, tech stack, struktur folder | Referensi |
| [02-backend-logic.md](02-backend-logic.md) | Logic backend Laravel: routing, auth, CRUD, upload, notifikasi | Normatif |
| [03-database.md](03-database.md) | Tabel, relasi, pola singleton vs list | Normatif |
| [04-alur-bisnis.md](04-alur-bisnis.md) | Alur end-to-end semua peran | Normatif |
| [05-api-reference.md](05-api-reference.md) | Kontrak endpoint (URI + payload, dibekukan) | Normatif — dibekukan |
| [06-deployment.md](06-deployment.md) | Deploy Docker multi-arch (`arm64` dev, `amd64` prod) | Normatif |
| [07-cms.md](07-cms.md) | Peta section → tabel → halaman admin → API + aturan khusus | Normatif |
| [08-migrasi.md](08-migrasi.md) | Rencana migrasi Express→Laravel, SolidJS→Svelte per fase | Rencana kerja |

Target stack: backend **Laravel**, frontend **SvelteKit SPA statis**, DB **MySQL 8**, deploy **Docker Compose multi-arch**. Desain UI mengacu Figma via `DESIGN.md` (tetap jadi referensi visual).

## Cara memakai
1. Mulai dari [00-status.md](00-status.md) untuk konteks (keputusan, status, gap).
2. Implementasi mengikuti file normatif sesuai area; kontrak [05](05-api-reference.md) tidak boleh berubah tanpa keputusan baru di [00-status](00-status.md).
3. Setiap perubahan perilaku: update file `docs/` terkait **di commit yang sama** dengan kode; keputusan baru dicatat di [00-status](00-status.md).
