# FETCH Role: Warga (Full Response)

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_warga"
```

## 1) Auth/Profile

Endpoint:
- `GET /auth/me`
- `POST /auth/refresh`
- `POST /auth/logout`
- `GET /users/profile`
- `PUT /users/profile`
- `GET /check-token`

Contoh `GET /auth/me`:

```bash
curl -X GET "$BASE_URL/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body

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
    "no_telepon": "08111111111",
    "alamat": "RT 03 RW 02",
    "id_desa": 1,
    "desa": {
      "id": 1,
      "nama": "Sukamaju",
      "kecamatan": {
        "id": 12,
        "nama": "Kec. Tengah",
        "kabupaten": {
          "id": 5,
          "nama": "Kab. Maju",
          "provinsi": {
            "id": 2,
            "nama": "Jawa Barat"
          }
        }
      }
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 401 Response Body

```json
{
  "success": false,
  "message": "Tidak terautentikasi",
  "code": "UNAUTHORIZED",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) Laporan Warga

Endpoint:
- `GET /laporans`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`
- `GET /laporans/{id}/riwayat`

Contoh `GET /laporans?status=Diproses&limit=10`:

```bash
curl -X GET "$BASE_URL/laporans?status=Diproses&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (List + Nested)

```json
{
  "success": true,
  "message": "Data laporan berhasil diambil",
  "data": [
    {
      "id": 10,
      "id_pelapor": 5,
      "id_kategori_bencana": 1,
      "id_desa": 1,
      "judul_laporan": "Banjir RT 03",
      "deskripsi": "Air setinggi lutut",
      "tingkat_keparahan": "Tinggi",
      "status": "Diproses",
      "latitude": -6.2,
      "longitude": 106.8,
      "alamat_lengkap": "RT 03 RW 02",
      "is_prioritas": true,
      "view_count": 7,
      "jumlah_korban": 2,
      "jumlah_rumah_rusak": 5,
      "pelapor": {
        "id": 5,
        "nama": "Andi Warga"
      },
      "kategori": {
        "id": 1,
        "nama_kategori": "Banjir"
      },
      "desa": {
        "id": 1,
        "nama": "Sukamaju"
      },
      "monitoring": [],
      "tindak_lanjut": []
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 4,
      "per_page": 10,
      "total": 37,
      "from": 1,
      "to": 10
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

Contoh `POST /laporans`:

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "judul_laporan":"Banjir RT 03",
    "deskripsi":"Air setinggi lutut",
    "tingkat_keparahan":"Tinggi",
    "latitude":-6.2,
    "longitude":106.8,
    "id_kategori_bencana":1,
    "id_desa":1
  }'
```

### 201 Response Body

```json
{
  "success": true,
  "message": "Laporan berhasil dibuat",
  "data": {
    "id": 88,
    "id_pelapor": 5,
    "judul_laporan": "Banjir RT 03",
    "status": "Draft",
    "pelapor": {
      "id": 5,
      "nama": "Andi Warga"
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Response Body

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "judul_laporan": [
      "The judul laporan field is required."
    ],
    "id_desa": [
      "The selected id desa is invalid."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 403 Response Body (update/delete laporan bukan milik sendiri)

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk mengubah laporan ini",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) Referensi yang Bisa Diakses

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
- seluruh endpoint public `wilayah`
- endpoint public `bmkg`

## 4) Endpoint yang Ditolak untuk Warga

- `/monitoring/*`
- `/tindak-lanjut/*`
- `/riwayat-tindakan/*`

### 403 Response Body

```json
{
  "success": false,
  "message": "Warga tidak memiliki akses ke data monitoring operasional",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) Matriks Status Semua Endpoint Warga

- `GET /auth/me`
  - `200` -> lihat section `200 Response Body`
  - `401` -> lihat section `401 Response Body`

- `POST /auth/refresh`
  - `200` ->

```json
{
  "success": true,
  "message": "Token berhasil diperbarui",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `401` -> body unauthorized standar

- `POST /auth/logout`
  - `200` ->

```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `401` -> body unauthorized standar

- `GET /users/profile`
  - `200` -> shape sama dengan `GET /auth/me`
  - `401` -> body unauthorized standar

- `PUT /users/profile`
  - `200` ->

```json
{
  "success": true,
  "message": "Profil berhasil diupdate",
  "data": {
    "id": 5,
    "nama": "Warga Update",
    "username": "andiw",
    "email": "andi@example.com",
    "role": "Warga",
    "role_label": "Warga",
    "no_telepon": "081234567890",
    "alamat": "Jl. Merdeka 1",
    "id_desa": 1,
    "updated_at": "2026-03-20T10:00:00.000000Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `401` -> body unauthorized standar
  - `422` -> body validasi standar (lihat section 422 di dokumen ini)

- `GET /check-token`
  - `200` ->

```json
{
  "success": true,
  "message": "Token valid",
  "data": {
    "user_id": 5,
    "user_role": "Warga",
    "user_name": "Andi Warga"
  }
}
```

  - `401` -> unauthorized

- `GET /laporans`
  - `200` -> lihat section `200 Response Body (List + Nested)`

- `POST /laporans`
  - `201` -> lihat section `201 Response Body`
  - `422` -> lihat section `422 Response Body`
  - `401` -> unauthorized
  - `500` -> internal error standar

- `GET /laporans/{id}`
  - `200` -> shape nested lengkap seperti list item (plus detail field tambahan)
  - `404` -> resource not found

- `PUT /laporans/{id}`
  - `200` -> shape detail laporan setelah update
  - `403` -> lihat section `403 Response Body`
  - `422` -> validasi

- `DELETE /laporans/{id}`
  - `200` ->

```json
{
  "success": true,
  "message": "Laporan berhasil dihapus",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `403` -> denied by ownership/policy

- `GET /laporans/statistics`
  - `200` -> shape statistik sama seperti dokumen admin (tanpa perubahan contract)

- `GET /laporans/{id}/riwayat`
  - `200` ->

```json
{
  "success": true,
  "message": "Riwayat laporan berhasil diambil",
  "data": [
    {
      "id": 21,
      "tindaklanjut_id": 7,
      "id_petugas": 3,
      "keterangan": "Evakuasi tahap awal",
      "waktu_tindakan": "2026-03-20 10:00:00"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `404` -> laporan tidak ditemukan
