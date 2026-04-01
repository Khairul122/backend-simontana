# FETCH Role: Warga

Panduan endpoint yang relevan untuk role `Warga`, termasuk request body, CURL, response, dan batasan policy.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_warga"
```

Header protected:

- `Accept: application/json`
- `Authorization: Bearer $TOKEN`

## 1) Session dan Profil

### GET /auth/me

```bash
curl -X GET "$BASE_URL/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Contoh response:

```json
{
  "success": true,
  "message": "Data user berhasil diambil",
  "data": {
    "id": 5,
    "nama": "Andi Warga",
    "username": "andiw",
    "email": "andi@example.com",
    "role": "Warga",
    "role_label": "Warga",
    "id_desa": 1,
    "desa": {
      "id": 1,
      "nama": "Sukamaju"
    }
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### GET /users/profile

```bash
curl -X GET "$BASE_URL/users/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### PUT /users/profile

Request body:

```json
{
  "nama": "Andi Warga Update",
  "no_telepon": "081234567890",
  "alamat": "Jl. Merdeka No. 1",
  "id_desa": 1
}
```

```bash
curl -X PUT "$BASE_URL/users/profile" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama":"Andi Warga Update",
    "no_telepon":"081234567890",
    "alamat":"Jl. Merdeka No. 1",
    "id_desa":1
  }'
```

### POST /auth/refresh

```bash
curl -X POST "$BASE_URL/auth/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### POST /auth/logout

```bash
curl -X POST "$BASE_URL/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 2) Laporan Warga

Endpoint utama:

- `GET /laporans`
- `GET /laporans/pelapor/{pelaporId}`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`
- `GET /laporans/{id}/riwayat`
- `GET /warga/laporans/{id}/detail-lengkap`

### GET /laporans?status=Diproses&limit=10

```bash
curl -X GET "$BASE_URL/laporans?status=Diproses&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### GET /laporans/pelapor/{pelaporId}

Untuk `Warga`, nilai `pelaporId` harus ID diri sendiri.

```bash
curl -X GET "$BASE_URL/laporans/pelapor/5?status=Draft&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Jika memanggil ID pelapor lain:

```json
{
  "success": false,
  "message": "Warga hanya dapat melihat laporan miliknya sendiri",
  "code": "INSUFFICIENT_PERMISSIONS"
}
```

### POST /laporans (JSON)

Request body:

```json
{
  "judul_laporan": "Banjir RT 03",
  "deskripsi": "Air setinggi lutut",
  "tingkat_keparahan": "Tinggi",
  "status": "Draft",
  "latitude": -6.2,
  "longitude": 106.8,
  "id_kategori_bencana": 1,
  "id_desa": 1,
  "alamat_laporan": "RT 03 RW 02",
  "jumlah_korban": 2,
  "jumlah_rumah_rusak": 5,
  "is_prioritas": true,
  "data_tambahan": {
    "ketinggian_air_cm": 80
  }
}
```

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "judul_laporan":"Banjir RT 03",
    "deskripsi":"Air setinggi lutut",
    "tingkat_keparahan":"Tinggi",
    "status":"Draft",
    "latitude":-6.2,
    "longitude":106.8,
    "id_kategori_bencana":1,
    "id_desa":1,
    "alamat_laporan":"RT 03 RW 02",
    "jumlah_korban":2,
    "jumlah_rumah_rusak":5,
    "is_prioritas":true,
    "data_tambahan":{"ketinggian_air_cm":80}
  }'
```

### POST /laporans (multipart upload)

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "judul_laporan=Banjir RT 03" \
  -F "deskripsi=Air setinggi lutut" \
  -F "tingkat_keparahan=Tinggi" \
  -F "latitude=-6.2" \
  -F "longitude=106.8" \
  -F "id_kategori_bencana=1" \
  -F "id_desa=1" \
  -F "alamat_laporan=RT 03 RW 02" \
  -F "is_prioritas=true" \
  -F "foto_bukti_1=@./sample1.jpg" \
  -F "video_bukti=@./sample.mp4"
```

Validasi file:

- `foto_bukti_1/2/3`: `jpeg|jpg|png`, max 5MB
- `video_bukti`: `mp4|avi|mov`, max 10MB

### PUT /laporans/{id}

```bash
curl -X PUT "$BASE_URL/laporans/10" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "deskripsi":"Air naik sampai paha",
    "tingkat_keparahan":"Kritis",
    "is_prioritas":true
  }'
```

### DELETE /laporans/{id}

```bash
curl -X DELETE "$BASE_URL/laporans/10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Response sukses:

```json
{
  "success": true,
  "message": "Laporan berhasil dihapus",
  "data": null,
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### GET /warga/laporans/{id}/detail-lengkap

Endpoint agregasi untuk halaman detail warga.

```bash
curl -X GET "$BASE_URL/warga/laporans/10/detail-lengkap" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Response membawa tiga blok:

- `data.detail_laporan`
- `data.tindak_lanjut`
- `data.riwayat_tindakan`

## 3) Operasional yang Bisa Diakses Warga

- `GET /tindak-lanjut` (hanya laporan milik sendiri)
- `GET /tindak-lanjut/{id}` (hanya jika terkait laporan sendiri)
- `GET /riwayat-tindakan` (hanya laporan milik sendiri)
- `GET /riwayat-tindakan/{id}` (hanya jika terkait laporan sendiri)
- `GET /monitoring*` ditolak untuk Warga

Contoh list tindak lanjut:

```bash
curl -X GET "$BASE_URL/tindak-lanjut?per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 4) Referensi untuk Warga

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
- semua endpoint public wilayah
- semua endpoint public BMKG

## 5) Error Contract yang Sering Muncul

Semua response error di endpoint warga mengikuti kontrak global dan menyertakan `request_id`.

### 401

```json
{
  "success": false,
  "message": "Token tidak valid",
  "code": "UNAUTHORIZED",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 403

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk melihat tindak lanjut ini",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 422

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "id_desa": [
      "The selected id desa is invalid."
    ]
  },
  "details": {
    "id_desa": [
      "The selected id desa is invalid."
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```
