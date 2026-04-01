# FETCH API Guide (Master)

Panduan utama konsumsi API backend SIMONTA BENCANA.

- Base API utama: `http://127.0.0.1:8000/api/v1`
- Legacy API (`/api/*`) masih aktif untuk backward compatibility
- Semua contoh di dokumen ini memakai endpoint `v1`

## Peta Dokumen

- `FETCH_PUBLIC.md` -> endpoint publik (tanpa token)
- `FETCH_BMKG.md` -> endpoint BMKG (public + protected)
- `FETCH_WARGA.md` -> alur untuk role `Warga`
- `FETCH_OPERATOR_DESA.md` -> alur untuk role `OperatorDesa`
- `FETCH_PETUGAS_BPBD.md` -> alur untuk role `PetugasBPBD`
- `FETCH_ADMIN.md` -> alur untuk role `Admin`

## Variabel Shell Dasar

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="isi_jwt_di_sini"
```

## Header Standar

- Selalu kirim `Accept: application/json`
- Endpoint protected wajib `Authorization: Bearer $TOKEN`
- Request JSON pakai `Content-Type: application/json`
- Multipart upload (`-F`) jangan set `Content-Type` manual

## Kontrak Response Global

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
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

Catatan:

- `meta.pagination` hanya muncul di endpoint paginated
- `data` bisa `null` pada `logout`/`delete`
- `request_id` biasanya ada pada response dari controller helper

### Error

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "field": [
      "Pesan error"
    ]
  },
  "details": {
    "field": [
      "Pesan error"
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## Mapping Status Code

- `200` sukses read/update/delete tertentu
- `201` resource berhasil dibuat
- `400` request salah/constraint domain gagal
- `401` token tidak ada/invalid/expired
- `403` role/policy tidak mengizinkan
- `404` resource tidak ditemukan
- `422` validasi gagal atau transisi status invalid
- `429` rate limit
- `500` internal server error

## Ringkasan Endpoint Per Domain

### Auth

- `POST /auth/register`
- `POST /auth/login`
- `GET /auth/roles`
- `GET /auth/me`
- `POST /auth/refresh`
- `POST /auth/logout`
- `GET /check-token`

### Users

- `GET /users/profile`
- `PUT /users/profile`
- `GET /users` (Admin)
- `POST /users` (Admin)
- `GET /users/statistics` (Admin)
- `GET /users/{id}` (Admin)
- `PUT /users/{id}` (Admin)
- `DELETE /users/{id}` (Admin)

### Laporan + Workflow

- `GET /laporans`
- `GET /laporans/pelapor/{pelaporId}`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`
- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`
- `GET /warga/laporans/{id}/detail-lengkap` (Warga only)

### Operasional

- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`

### Kategori Bencana (protected)

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
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

Admin write:

- `POST /wilayah`
- `PUT /wilayah/{id}`
- `DELETE /wilayah/{id}`
- `POST/PUT/DELETE /wilayah/provinsi`
- `POST/PUT/DELETE /wilayah/kabupaten`
- `POST/PUT/DELETE /wilayah/kecamatan`
- `POST/PUT/DELETE /wilayah/desa`

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

## Tips CURL yang Penting

### Simpan token dari login

```bash
TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}' | jq -r '.data.token')
```

### Upload multipart laporan

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "judul_laporan=Banjir RT 03" \
  -F "deskripsi=Air naik setinggi lutut" \
  -F "tingkat_keparahan=Sedang" \
  -F "latitude=-6.2" \
  -F "longitude=106.8" \
  -F "id_kategori_bencana=1" \
  -F "id_desa=1" \
  -F "alamat_laporan=RT 03 RW 02" \
  -F "is_prioritas=true" \
  -F "foto_bukti_1=@./sample1.jpg"
```

### Check status code + body sekaligus

```bash
curl -i -X GET "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## Troubleshooting Cepat

- `401` -> cek format `Authorization: Bearer <token>`
- `403` -> role/policy tidak memenuhi
- `404` -> pastikan ID resource benar
- `422` -> cek field required, enum, dan tipe data
- URL file upload tidak bisa diakses -> jalankan `php artisan storage:link`
- butuh trace request -> pakai `X-Request-Id` sendiri saat request

## End-to-End Flow (Contoh Nyata)

Skenario berikut memperlihatkan alur lengkap dari registrasi warga hingga laporan selesai diproses.

### Langkah 1 - Register Warga

```bash
curl -X POST "$BASE_URL/auth/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "nama":"Warga Demo",
    "username":"warga_demo",
    "email":"warga_demo@example.com",
    "password":"password123",
    "password_confirmation":"password123",
    "id_desa":1
  }'
```

### Langkah 2 - Login Warga dan simpan token

```bash
WARGA_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"warga_demo","password":"password123"}' | jq -r '.data.token')
```

### Langkah 3 - Warga buat laporan

```bash
LAPORAN_ID=$(curl -s -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" \
  -d '{
    "judul_laporan":"Banjir di RT 03",
    "deskripsi":"Air naik setinggi lutut",
    "tingkat_keparahan":"Sedang",
    "latitude":-6.2,
    "longitude":106.8,
    "id_kategori_bencana":1,
    "id_desa":1,
    "alamat_laporan":"RT 03 RW 02",
    "status":"Menunggu Verifikasi"
  }' | jq -r '.data.id')
```

### Langkah 4 - Login Petugas/Operator/Admin

```bash
PETUGAS_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"petugas_demo","password":"password123"}' | jq -r '.data.token')
```

### Langkah 5 - Verifikasi laporan

```bash
curl -X POST "$BASE_URL/laporans/$LAPORAN_ID/verifikasi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $PETUGAS_TOKEN" \
  -d '{
    "status":"Diverifikasi",
    "catatan_verifikasi":"Laporan valid"
  }'
```

### Langkah 6 - Proses laporan

```bash
curl -X POST "$BASE_URL/laporans/$LAPORAN_ID/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $PETUGAS_TOKEN" \
  -d '{"status":"Diproses"}'
```

### Langkah 7 - Selesaikan laporan

```bash
curl -X POST "$BASE_URL/laporans/$LAPORAN_ID/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $PETUGAS_TOKEN" \
  -d '{"status":"Selesai"}'
```

### Langkah 8 - Warga lihat detail agregasi

```bash
curl -X GET "$BASE_URL/warga/laporans/$LAPORAN_ID/detail-lengkap" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN"
```

Output akhir endpoint agregasi:

- `data.detail_laporan` memuat data laporan terbaru,
- `data.tindak_lanjut` memuat daftar tindak lanjut,
- `data.riwayat_tindakan` memuat riwayat aksi lapangan.

## Checklist QA Dokumentasi

Gunakan checklist ini sebelum handoff ke frontend/QA:

- base URL semua contoh adalah `/api/v1`,
- contoh endpoint protected selalu mengandung `Authorization: Bearer <token>`,
- field wajib request sudah sesuai validator request class,
- enum status/workflow sesuai endpoint (`verifikasi` vs `proses`),
- contoh response memakai kontrak global (`success`, `message`, `data`, `code`, `errors`, `details`, `request_id`),
- error penting sudah dicontohkan (`401`, `403`, `404`, `422`, `429`, `500`),
- endpoint lintas role tidak bocor ke role yang tidak berhak,
- endpoint publik register menjelaskan `Warga only`.

## Postman Import Guide

Gunakan panduan ini supaya collection tim FE/QA konsisten.

### Struktur Collection yang direkomendasikan

- `00 Auth`
- `01 Public`
- `02 Warga`
- `03 OperatorDesa`
- `04 PetugasBPBD`
- `05 Admin`
- `06 BMKG`

### Environment Variable

Set di Postman Environment:

- `base_url` -> `http://127.0.0.1:8000/api/v1`
- `token` -> kosongkan dulu
- `request_id` -> `{{$guid}}`

### Header Default Collection

- `Accept: application/json`
- `X-Request-Id: {{request_id}}`

Untuk endpoint protected, tambahkan:

- `Authorization: Bearer {{token}}`

### Script otomatis simpan token setelah login

Taruh pada tab `Tests` request login:

```javascript
const json = pm.response.json();
if (json?.data?.token) {
  pm.environment.set("token", json.data.token);
}
```

### Praktik import cURL ke Postman

- pastikan URL yang diimport selalu memakai `{{base_url}}`
- untuk multipart upload, cek mode body sudah `form-data`
- hindari hardcode token di request, gunakan variable `{{token}}`

## Quick Smoke Script (10 Cek Inti)

Pakai urutan ini untuk sanity check cepat setelah deploy/staging refresh.

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"

# 1) Public health-like check (roles)
curl -s -X GET "$BASE_URL/auth/roles" -H "Accept: application/json"

# 2) Public wilayah index
curl -s -X GET "$BASE_URL/wilayah?jenis=desa&per_page=5" -H "Accept: application/json"

# 3) Public BMKG terbaru
curl -s -X GET "$BASE_URL/bmkg/gempa/terbaru" -H "Accept: application/json"

# 4) Login admin
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}' | jq -r '.data.token')

# 5) Check token admin
curl -s -X GET "$BASE_URL/check-token" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 6) Users list admin
curl -s -X GET "$BASE_URL/users?per_page=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 7) Laporan list admin
curl -s -X GET "$BASE_URL/laporans?limit=5" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 8) Statistik laporan admin
curl -s -X GET "$BASE_URL/laporans/statistics?period=monthly" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 9) BMKG protected cache status
curl -s -X GET "$BASE_URL/bmkg/cache/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"

# 10) Refresh token
curl -s -X POST "$BASE_URL/auth/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN"
```

Tips eksekusi:

- tambah `| jq` di tiap command untuk output lebih rapi,
- jika ingin lihat status code, gunakan `curl -i`.

## Validator Matrix (Write Endpoints)

Ringkasan field wajib/enum untuk endpoint tulis yang paling sering dipakai QA.

### Auth

`POST /auth/register`

- required: `nama`, `username`, `email`, `password`, `password_confirmation`
- optional: `role` (jika dikirim hanya `Warga`), `no_telepon`, `alamat`, `id_desa`

`POST /auth/login`

- required: `username` (bisa username/email), `password`

### Users (Admin)

`POST /users`

- required: `nama`, `username`, `email`, `password`, `password_confirmation`, `role`
- role enum: `Admin|PetugasBPBD|OperatorDesa|Warga`

`PUT /users/{id}`

- required: `nama`, `email`
- optional: `password`, `no_telepon`, `alamat`, `id_desa`

### Laporans

`POST /laporans`

- required: `judul_laporan`, `deskripsi`, `tingkat_keparahan`, `latitude`, `longitude`, `id_kategori_bencana`, `id_desa`
- optional: `status`, `alamat_laporan`, `jumlah_korban`, `jumlah_rumah_rusak`, `is_prioritas`, `data_tambahan`, `waktu_laporan`, file upload
- enum `tingkat_keparahan`: `Rendah|Sedang|Tinggi|Kritis`
- enum `status`: `Draft|Menunggu Verifikasi`

`PUT /laporans/{id}`

- semua field bersifat optional (`sometimes`)
- enum sama dengan create untuk `tingkat_keparahan` dan `status`

### Workflow

`POST /laporans/{id}/verifikasi`

- required: `status`
- enum: `Diverifikasi|Ditolak`
- optional: `catatan_verifikasi`

`POST /laporans/{id}/proses`

- required: `status`
- enum: `Diproses|Selesai`

### Monitoring

`POST /monitoring`

- required: `id_laporan`, `id_operator`, `waktu_monitoring`, `hasil_monitoring`
- optional: `koordinat_gps`

`PUT /monitoring/{id}`

- optional: `id_laporan`, `id_operator`, `waktu_monitoring`, `hasil_monitoring`, `koordinat_gps`
- catatan policy: non-admin tidak boleh ganti operator selain dirinya

### Tindak Lanjut

`POST /tindak-lanjut`

- required: `laporan_id`, `id_petugas`, `tanggal_tanggapan`
- optional: `status`
- enum `status`: `Menuju Lokasi|Selesai`

`PUT /tindak-lanjut/{id}`

- optional: `tanggal_tanggapan`, `status`
- enum `status`: `Menuju Lokasi|Selesai`

### Riwayat Tindakan

`POST /riwayat-tindakan`

- required: `tindaklanjut_id`, `id_petugas`, `keterangan`, `waktu_tindakan`

`PUT /riwayat-tindakan/{id}`

- optional: `keterangan`, `waktu_tindakan`

## Endpoint by Role (Ringkas)

Gunakan matriks ini untuk onboarding cepat FE/QA terkait batasan akses.

| Endpoint Group | Public | Warga | OperatorDesa | PetugasBPBD | Admin |
| --- | --- | --- | --- | --- | --- |
| `POST /auth/register`, `POST /auth/login`, `GET /auth/roles` | Ya | Ya | Ya | Ya | Ya |
| `GET/POST /auth/me|refresh|logout`, `GET /check-token` | Tidak | Ya | Ya | Ya | Ya |
| `GET /users/profile`, `PUT /users/profile` | Tidak | Ya | Ya | Ya | Ya |
| `GET/POST/PUT/DELETE /users*` | Tidak | Tidak | Tidak | Tidak | Ya |
| `GET /laporans`, `GET /laporans/{id}`, `GET /laporans/statistics` | Tidak | Ya | Ya | Ya | Ya |
| `GET /laporans/pelapor/{id}` | Tidak | Ya (ID sendiri) | Ya | Ya | Ya |
| `POST /laporans`, `PUT /laporans/{id}`, `DELETE /laporans/{id}` | Tidak | Ya (policy) | Ya (policy) | Ya (policy update) | Ya |
| `POST /laporans/{id}/verifikasi`, `POST /laporans/{id}/proses` | Tidak | Tidak | Ya | Ya | Ya |
| `GET /laporans/{id}/riwayat` | Tidak | Ya | Ya | Ya | Ya |
| `GET /warga/laporans/{id}/detail-lengkap` | Tidak | Ya | Tidak | Tidak | Tidak |
| `GET /monitoring*` | Tidak | Tidak | Ya | Ya | Ya |
| `POST/PUT/DELETE /monitoring*` | Tidak | Tidak | Ya (policy) | Ya (policy) | Ya |
| `GET /tindak-lanjut*`, `GET /riwayat-tindakan*` | Tidak | Ya (scoped) | Ya | Ya | Ya |
| `POST/PUT/DELETE /tindak-lanjut*`, `/riwayat-tindakan*` | Tidak | Tidak | Ya (policy) | Ya (policy) | Ya |
| `GET /kategori-bencana*` | Tidak | Ya | Ya | Ya | Ya |
| `POST/PUT/PATCH/DELETE /kategori-bencana*` | Tidak | Tidak | Tidak | Tidak | Ya |
| `GET /wilayah*` | Ya | Ya | Ya | Ya | Ya |
| `POST/PUT/DELETE /wilayah*` | Tidak | Tidak | Tidak | Tidak | Ya |
| `GET /bmkg/gempa*`, `/bmkg/prakiraan-cuaca`, `/bmkg/peringatan-dini-cuaca` | Ya | Ya | Ya | Ya | Ya |
| `GET /bmkg`, `GET /bmkg/cache/status`, `POST /bmkg/cache/clear` | Tidak | Ya | Ya | Ya | Ya |

Catatan:

- Untuk endpoint bertanda `policy`, akses final ditentukan oleh policy object-level (kepemilikan, assignment, atau peran domain).
- Untuk Warga pada endpoint scoped, response otomatis dibatasi ke data milik sendiri.
