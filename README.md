# SIMONTA BENCANA Backend API

Backend API untuk sistem pelaporan dan penanganan bencana berbasis role (Admin, PetugasBPBD, OperatorDesa, Warga), dibangun dengan Laravel 12 dan autentikasi JWT.

## Fitur Utama

- Autentikasi JWT: login, register, refresh token, logout, profil user.
- Role-based access control: pembatasan akses endpoint berdasarkan role.
- Manajemen laporan bencana: CRUD laporan, statistik, dan workflow verifikasi/proses.
- Modul operasional: monitoring, tindak lanjut, riwayat tindakan.
- Master data wilayah: provinsi, kabupaten, kecamatan, desa (listing + CRUD admin).
- Integrasi BMKG: gempa terbaru/terkini/dirasakan, peringatan tsunami, prakiraan cuaca.
- API documentation: OpenAPI/Swagger (`l5-swagger`).
- Kontrak response API konsisten (`success`, `message`, `data`, `code`, `request_id`).

## Tech Stack

- PHP `^8.2`
- Laravel `^12.0`
- JWT Auth: `tymon/jwt-auth`
- Swagger: `darkaonline/l5-swagger`
- FCM: `edwinhoksberg/php-fcm`
- Database: MySQL/SQLite (konfigurasi via `.env`)

## Struktur Modul API

Berikut grup endpoint utama dari `routes/api.php`:

- `auth/*`: login, register, me, refresh, logout, roles.
- `users/*`: profil user + manajemen user (admin).
- `laporans/*`: CRUD laporan, statistik, workflow (`verifikasi`, `proses`, `riwayat`).
- `monitoring/*`: CRUD monitoring operasional.
- `tindak-lanjut/*`: CRUD tindak lanjut.
- `riwayat-tindakan/*`: CRUD riwayat tindakan.
- `kategori-bencana/*`: referensi kategori bencana (CRUD admin).
- `wilayah/*`: listing/hierarchy/reference + CRUD admin.
- `bmkg/*`: endpoint data BMKG (public + protected cache management).

## Prasyarat

- PHP 8.2+
- Composer 2+
- MySQL 8+ (disarankan untuk environment dev utama)
- Node.js 18+ dan npm (jika jalankan Vite/build frontend assets)

## Instalasi

1) Clone repository dan install dependency:

```bash
composer install
```

2) Buat file environment:

```bash
cp .env.example .env
```

3) Generate app key:

```bash
php artisan key:generate
```

4) Atur koneksi database di `.env`, lalu migrate:

```bash
php artisan migrate
```

5) (Opsional) install JS dependencies:

```bash
npm install
```

## Menjalankan Aplikasi

### Mode standar

```bash
php artisan serve
```

API default akan tersedia di:

- `http://127.0.0.1:8000/api`

### Mode development terintegrasi (composer script)

Menjalankan server + queue + logs + vite sekaligus:

```bash
composer run dev
```

## Dokumentasi API (Swagger)

Generate docs:

```bash
php artisan l5-swagger:generate
```

Akses docs:

- `http://127.0.0.1:8000/api/documentation`

## Auth dan Security

- Public auth endpoint:
  - `POST /api/auth/login`
  - `POST /api/auth/register`
- Kedua endpoint auth publik sudah menggunakan throttle limiter.
- Endpoint protected menggunakan middleware JWT (`jwt.auth`).
- Authorization berbasis role dan policy per-record pada modul operasional.

## Kontrak Response API

### Sukses

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "request_id": "req_xxx"
}
```

### Gagal

```json
{
  "success": false,
  "message": "Error",
  "code": "ERROR_CODE",
  "details": {},
  "request_id": "req_xxx"
}
```

## Testing

Menjalankan seluruh test:

```bash
php artisan test
```

Menjalankan test tertentu:

```bash
php artisan test --filter=SecurityWorkflowPerformanceTest
```

Catatan:

- Konfigurasi test ada di `phpunit.xml`.
- Environment test saat ini diset ke MySQL (`simonta_bencana_test`). Pastikan DB test tersedia.

## Optimasi Performa (Development)

Build cache framework:

```bash
php artisan optimize
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

Membersihkan cache saat debugging:

```bash
php artisan optimize:clear
```

## Benchmark Endpoint

Project menyediakan script benchmark endpoint:

- Script: `scripts/benchmark_endpoints.php`
- Output CSV: `storage/logs/api-benchmark-latency.csv`

Jalankan benchmark:

```bash
php scripts/benchmark_endpoints.php
```

## Panduan Fetch Endpoint

Dokumentasi curl endpoint dipisah per role:

- `FETCH.md` (index)
- `FETCH_PUBLIC.md`
- `FETCH_WARGA.md`
- `FETCH_OPERATOR_DESA.md`
- `FETCH_PETUGAS_BPBD.md`
- `FETCH_ADMIN.md`

## Kontribusi

Alur kontribusi yang disarankan:

1. Buat branch fitur/perbaikan.
2. Implementasi perubahan + test.
3. Jalankan lint/test lokal.
4. Buat commit yang jelas dan kecil.
5. Ajukan PR.

## Troubleshooting Singkat

- `401 TOKEN_MISSING`: pastikan header `Authorization: Bearer <token>` dikirim.
- `401 TOKEN_INVALID/TOKEN_EXPIRED`: login ulang atau refresh token.
- `403 INSUFFICIENT_PERMISSIONS`: role user tidak sesuai endpoint.
- `422 VALIDATION_ERROR`: cek field request sesuai validasi endpoint.
- Swagger kosong/tidak update: jalankan `php artisan l5-swagger:generate`.

## Lisensi

Project ini mengikuti lisensi yang tercantum pada `composer.json` (MIT untuk skeleton Laravel).
