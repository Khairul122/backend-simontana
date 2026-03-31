# FETCH API Guide (Master)

Dokumen ini adalah panduan utama konsumsi API untuk backend SIMONTA BENCANA.
Semua endpoint di bawah menggunakan versi `v1` sebagai source of truth.

## Daftar Dokumen Per Konteks

- `FETCH_PUBLIC.md` -> endpoint publik (tanpa JWT)
- `FETCH_BMKG.md` -> endpoint BMKG (public + protected cache)
- `FETCH_WARGA.md` -> endpoint untuk role `Warga`
- `FETCH_OPERATOR_DESA.md` -> endpoint untuk role `OperatorDesa`
- `FETCH_PETUGAS_BPBD.md` -> endpoint untuk role `PetugasBPBD`
- `FETCH_ADMIN.md` -> endpoint untuk role `Admin`

## Base URL, Header, dan Variabel Shell

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="isi_token_jwt"
```

Header yang dipakai hampir di semua call:

- `Accept: application/json`
- `Authorization: Bearer $TOKEN` (endpoint protected)
- `Content-Type: application/json` (request JSON)
- Jangan pakai `Content-Type: application/json` untuk multipart upload (`-F`), biarkan `curl` set otomatis.

## Kontrak Response Global

### Success Contract

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

- `meta.pagination` hanya muncul pada endpoint paginated.
- Untuk beberapa endpoint (`delete`, `logout`, dsb), `data` bisa `null`.
- `request_id` dipakai untuk tracing log backend.

### Error Contract

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "field": [
      "The field is required."
    ]
  },
  "details": {
    "field": [
      "The field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

Catatan:

- `errors` + `details` selalu ada pada `422` validasi.
- `code` umum: `BAD_REQUEST`, `UNAUTHORIZED`, `FORBIDDEN`, `INSUFFICIENT_PERMISSIONS`, `RESOURCE_NOT_FOUND`, `VALIDATION_ERROR`, `RATE_LIMITED`, `INTERNAL_SERVER_ERROR`.

## Status Code Reference

- `200` -> sukses GET/PUT/POST tertentu
- `201` -> resource berhasil dibuat
- `400` -> request tidak valid (misalnya query wajib tidak ada)
- `401` -> token invalid/tidak ada
- `403` -> role/policy menolak akses
- `404` -> resource/route tidak ditemukan
- `422` -> validasi gagal atau transisi status tidak valid
- `429` -> throttling/rate limit
- `500` -> internal/server/upstream error

## Ringkasan Domain Endpoint

### Auth

- `POST /auth/register`
- `POST /auth/login`
- `GET /auth/roles`
- `GET /auth/me` (JWT)
- `POST /auth/refresh` (JWT)
- `POST /auth/logout` (JWT)
- `GET /check-token` (JWT)

### Users

- `GET /users/profile` (JWT)
- `PUT /users/profile` (JWT)
- `GET /users` (Admin)
- `POST /users` (Admin)
- `GET /users/statistics` (Admin)
- `GET /users/{id}` (Admin)
- `PUT /users/{id}` (Admin)
- `DELETE /users/{id}` (Admin)

### Laporan

- `GET /laporans` (JWT)
- `GET /laporans/pelapor/{pelaporId}` (JWT)
- `POST /laporans` (JWT)
- `GET /laporans/{id}` (JWT)
- `PUT /laporans/{id}` (JWT)
- `DELETE /laporans/{id}` (JWT)
- `GET /laporans/statistics` (JWT)
- `GET /warga/laporans/{id}/detail-lengkap` (JWT, Warga only)

### Workflow Laporan

- `POST /laporans/{id}/verifikasi` (JWT)
- `POST /laporans/{id}/proses` (JWT)
- `GET /laporans/{id}/riwayat` (JWT)

### Operasional

- `GET/POST/PUT/DELETE /monitoring` (JWT)
- `GET/POST/PUT/DELETE /tindak-lanjut` (JWT)
- `GET/POST/PUT/DELETE /riwayat-tindakan` (JWT)

### Kategori Bencana

- `GET /kategori-bencana` (JWT)
- `GET /kategori-bencana/{id}` (JWT)
- `POST /kategori-bencana` (Admin)
- `PUT/PATCH /kategori-bencana/{id}` (Admin)
- `DELETE /kategori-bencana/{id}` (Admin)

### Wilayah

Public read:

- `GET /wilayah`
- `GET /wilayah/{id}`
- `GET /wilayah/provinsi`
- `GET /wilayah/provinsi/{id}`
- `GET /wilayah/kabupaten/{provinsi_id}`
- `GET /wilayah/kecamatan/{kabupaten_id}`
- `GET /wilayah/desa/{kecamatan_id}`
- `GET /wilayah/detail/{desa_id}`
- `GET /wilayah/hierarchy/{desa_id}`
- `GET /wilayah/search`

Admin CRUD:

- `POST /wilayah`
- `PUT /wilayah/{id}`
- `DELETE /wilayah/{id}`
- `POST|PUT|DELETE /wilayah/{level}` (provinsi/kabupaten/kecamatan/desa)

### BMKG

Public:

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=...`
- `GET /bmkg/peringatan-dini-cuaca`

Protected:

- `GET /bmkg`
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

## Praktik Request Upload Multipart (Laporan + Lampiran)

Contoh aman:

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "judul_laporan=Banjir di RT 03" \
  -F "deskripsi=Air naik setinggi lutut" \
  -F "tingkat_keparahan=Sedang" \
  -F "latitude=-6.2" \
  -F "longitude=106.8" \
  -F "id_kategori_bencana=1" \
  -F "id_desa=1" \
  -F "is_prioritas=true" \
  -F "foto_bukti_1=@./sample1.jpg" \
  -F "video_bukti=@./sample.mp4"
```

Catatan penting:

- `is_prioritas` boleh `true/false` (string) atau `1/0`.
- Untuk akses URL file (`/storage/laporans/...`), pastikan sudah menjalankan `php artisan storage:link`.
- Field file optional: `foto_bukti_1`, `foto_bukti_2`, `foto_bukti_3`, `video_bukti`.

## Troubleshooting Singkat

- `401 UNAUTHORIZED` -> token kosong/expired/salah format.
- `403 INSUFFICIENT_PERMISSIONS` -> role tidak diizinkan policy.
- `404 RESOURCE_NOT_FOUND` -> ID resource tidak ada.
- `422 VALIDATION_ERROR` -> cek required field, enum, tipe data, relasi `exists`.
- URL lampiran upload tidak bisa dibuka -> jalankan `php artisan storage:link`, cek `APP_URL`, dan pastikan file benar-benar ada di `storage/app/public/laporans`.
