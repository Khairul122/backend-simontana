# SIMONTA BENCANA Backend API

Backend API untuk sistem pelaporan dan penanganan bencana berbasis role `Admin`, `PetugasBPBD`, `OperatorDesa`, dan `Warga`.
Stack utama: Laravel 12 + JWT + RBAC + Policy + OpenAPI/Swagger.

Dokumen ini sudah disesuaikan dengan **breaking change kontrak API terbaru**:
- format response terstandar global,
- pagination selalu di `meta.pagination`,
- endpoint GET utama mengembalikan relasi nested lengkap (bukan hanya `id_*`).

## Ringkasan Fitur

- Autentikasi JWT: register, login, me, refresh, logout, roles.
- User management (Admin): CRUD user + statistik user.
- Laporan bencana: CRUD, statistik, workflow (`verifikasi`, `proses`, `riwayat`).
- Operasional: monitoring, tindak lanjut, riwayat tindakan.
- Wilayah: referensi + listing + hierarchy + search + CRUD admin.
- Kategori bencana: listing + detail + CRUD admin.
- Integrasi BMKG: endpoint public data gempa/cuaca/tsunami + endpoint protected untuk cache.
- OpenAPI/Swagger sudah sinkron dengan perubahan kontrak terbaru.

## Tech Stack

- PHP `^8.2`
- Laravel `^12.0`
- JWT: `tymon/jwt-auth`
- Swagger: `darkaonline/l5-swagger`
- FCM: `edwinhoksberg/php-fcm`
- Database: MySQL (utama) / SQLite (opsional)

## Struktur Endpoint

Sumber: `routes/api.php`.

- `auth/*`: register, login, roles, me, refresh, logout.
- `check-token`: validasi token dan ringkasan user.
- `users/*`: profile (semua role login) + CRUD/statistik (admin only).
- `laporans/*`: CRUD, statistik, workflow (`verifikasi`, `proses`, `riwayat`).
- `monitoring/*`: CRUD operasional monitoring.
- `tindak-lanjut/*`: CRUD operasional tindak lanjut.
- `riwayat-tindakan/*`: CRUD operasional riwayat tindakan.
- `kategori-bencana/*`: referensi kategori + CRUD admin.
- `wilayah/*`: reference/listing/detail/search/hierarchy + CRUD admin.
- `bmkg/*`: public feed + protected cache management.

## Prasyarat

- PHP 8.2+
- Composer 2+
- MySQL 8+ (direkomendasikan)
- Node.js 18+ (opsional untuk asset build)

## Instalasi dan Menjalankan Aplikasi

1. Install dependency PHP.

```bash
composer install
```

2. Siapkan environment.

```bash
cp .env.example .env
php artisan key:generate
```

3. Set DB dan CORS di `.env`.

Contoh variabel penting:

```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simonta_bencana
DB_USERNAME=root
DB_PASSWORD=

CORS_ALLOWED_ORIGINS=http://localhost:3000,http://127.0.0.1:3000,http://localhost:5173,http://127.0.0.1:5173
```

4. Migrasi database.

```bash
php artisan migrate
```

5. Jalankan server.

```bash
php artisan serve
```

Base API default: `http://127.0.0.1:8000/api`

## Kontrak Response Global (Breaking Change)

### Success

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {},
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 20,
      "total": 45,
      "from": 1,
      "to": 20
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

Catatan:
- `meta` hanya muncul saat dibutuhkan (terutama endpoint paginated).
- untuk delete sukses, `data` bisa `null`.

### Error

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "details": {},
  "errors": {
    "field": ["pesan error"]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## Standar Relasi Nested di Endpoint GET

Untuk endpoint GET domain utama (`laporans`, `monitoring`, `tindak-lanjut`, `riwayat-tindakan`, `users`, sebagian `wilayah`):

- field foreign key tetap ada (`id_pelapor`, `id_desa`, dll),
- sekaligus menampilkan objek relasi lengkap (`pelapor`, `desa`, `kategori`, `petugas`, `operator`, dll),
- termasuk nested wilayah bertingkat (`desa -> kecamatan -> kabupaten -> provinsi`).

Ini adalah perubahan kontrak yang disengaja untuk kebutuhan frontend yang memerlukan payload siap pakai.

## Auth dan Security

- Public auth endpoint:
  - `POST /api/auth/register`
  - `POST /api/auth/login`
  - `GET /api/auth/roles`
- Throttling:
  - login: limiter `auth-login`
  - register: limiter `auth-register`
- Protected endpoint: middleware `jwt.auth`.
- Authorization menggunakan role + policy (record-level).
- Error terstandar melalui global exception handler.

## Role Matrix Singkat

- `Admin`: akses penuh termasuk manajemen user, kategori, wilayah admin CRUD.
- `PetugasBPBD`: akses operasional tinggi, tetapi bukan manajemen user admin.
- `OperatorDesa`: akses operasional sesuai policy, bukan akses admin.
- `Warga`: fokus laporan pribadi/profil dan referensi; tidak boleh akses data operasional sensitif.

## Dokumentasi API (Swagger/OpenAPI)

Generate ulang dokumen:

```bash
php artisan l5-swagger:generate
```

Akses UI:

- `http://127.0.0.1:8000/api/documentation`

OpenAPI annotations berada di:
- `app/OpenApi/OpenApiSpec.php`
- `app/OpenApi/ApiSchemas.php`
- `app/OpenApi/ApiPaths.php`

## Testing

Jalankan seluruh test:

```bash
php artisan test
```

Jalankan test tertentu:

```bash
php artisan test --filter=SecurityWorkflowPerformanceTest
```

Catatan:
- konfigurasi test ada di `phpunit.xml`,
- gunakan DB test terpisah.

## Panduan Fetch

Dokumen fetch/curl dipisah per konteks role:

- `FETCH.md` (index + konvensi umum)
- `FETCH_PUBLIC.md`
- `FETCH_WARGA.md`
- `FETCH_OPERATOR_DESA.md`
- `FETCH_PETUGAS_BPBD.md`
- `FETCH_ADMIN.md`

## Troubleshooting

- `401 UNAUTHORIZED`/`TOKEN_INVALID`: cek header `Authorization: Bearer <token>`.
- `403 FORBIDDEN`/`INSUFFICIENT_PERMISSIONS`: role/policy tidak memenuhi.
- `422 VALIDATION_ERROR`: cek field body, enum, dan tipe data.
- `422 INVALID_STATUS_TRANSITION`: alur workflow laporan tidak valid.
- `429 RATE_LIMITED`: terlalu banyak request auth.
- Swagger tidak update: jalankan `php artisan l5-swagger:generate`.

## Lisensi

Mengikuti lisensi pada `composer.json` (MIT untuk skeleton Laravel).
