# FETCH Role: Warga (Lengkap)

Dokumen ini berisi endpoint yang umum dipakai role `Warga`, lengkap dengan body request, curl, dan body response.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_warga"
```

Header standar protected:

- `Accept: application/json`
- `Authorization: Bearer $TOKEN`

## 1) Profil dan Session

### A. GET `/auth/me`

#### CURL

```bash
curl -X GET "$BASE_URL/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

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

### B. POST `/auth/refresh`

#### CURL

```bash
curl -X POST "$BASE_URL/auth/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

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

### C. POST `/auth/logout`

#### CURL

```bash
curl -X POST "$BASE_URL/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### D. GET `/users/profile`

#### CURL

```bash
curl -X GET "$BASE_URL/users/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Profil berhasil diambil",
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
      "nama": "Sukamaju"
    },
    "created_at": "2026-03-01T10:00:00.000000Z",
    "updated_at": "2026-03-31T10:00:00.000000Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### E. PUT `/users/profile`

#### Full Request Body

```json
{
  "nama": "Andi Warga Update",
  "no_telepon": "081234567890",
  "alamat": "Jl. Merdeka No. 1",
  "id_desa": 1
}
```

#### CURL

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

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Profil berhasil diupdate",
  "data": {
    "id": 5,
    "nama": "Andi Warga Update",
    "username": "andiw",
    "email": "andi@example.com",
    "role": "Warga",
    "role_label": "Warga",
    "no_telepon": "081234567890",
    "alamat": "Jl. Merdeka No. 1",
    "id_desa": 1,
    "updated_at": "2026-03-31T10:00:00.000000Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) Laporan (Domain Utama Warga)

Endpoint:

- `GET /laporans`
- `GET /laporans/pelapor/{pelaporId}`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`
- `GET /laporans/{id}/riwayat`

### A. GET `/laporans?status=Diproses&limit=10`

#### CURL

```bash
curl -X GET "$BASE_URL/laporans?status=Diproses&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body (Paginated + Nested)

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
      "foto_bukti_1_url": "http://localhost:8000/storage/laporans/sample1.jpg",
      "video_bukti_url": "http://localhost:8000/storage/laporans/sample.mp4",
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

### B. GET `/laporans/pelapor/{pelaporId}`

Untuk role `Warga`, `pelaporId` harus milik sendiri.

#### CURL

```bash
curl -X GET "$BASE_URL/laporans/pelapor/5?status=Draft&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data laporan pelapor berhasil diambil",
  "data": [
    {
      "id": 10,
      "id_pelapor": 5,
      "judul_laporan": "Banjir RT 03",
      "status": "Draft",
      "pelapor": {
        "id": 5,
        "nama": "Andi Warga"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1,
      "from": 1,
      "to": 1
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

#### 403 Full Response Body (akses pelapor user lain)

```json
{
  "success": false,
  "message": "Warga hanya dapat melihat laporan miliknya sendiri",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### C. POST `/laporans` (JSON)

#### Full Request Body

```json
{
  "judul_laporan": "Banjir RT 03",
  "deskripsi": "Air setinggi lutut",
  "tingkat_keparahan": "Tinggi",
  "latitude": -6.2,
  "longitude": 106.8,
  "id_kategori_bencana": 1,
  "id_desa": 1,
  "alamat": "RT 03 RW 02",
  "jumlah_korban": 2,
  "jumlah_rumah_rusak": 5,
  "is_prioritas": true
}
```

#### CURL

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
    "id_desa":1,
    "alamat":"RT 03 RW 02",
    "jumlah_korban":2,
    "jumlah_rumah_rusak":5,
    "is_prioritas":true
  }'
```

#### 201 Full Response Body

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

### D. POST `/laporans` (Multipart + Lampiran)

Gunakan ini jika upload foto/video.

#### CURL

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
  -F "jumlah_korban=2" \
  -F "jumlah_rumah_rusak=5" \
  -F "is_prioritas=true" \
  -F "foto_bukti_1=@./sample1.jpg" \
  -F "foto_bukti_2=@./sample2.jpg" \
  -F "video_bukti=@./sample.mp4"
```

Catatan:

- `is_prioritas` bisa `true/false` atau `1/0`.
- Pastikan `php artisan storage:link` sudah dijalankan untuk akses URL `/storage/laporans/...`.

### E. 422 Full Response Body (validasi)

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
  "details": {
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

### F. PUT `/laporans/{id}`

#### Full Request Body

```json
{
  "deskripsi": "Air naik sampai paha",
  "tingkat_keparahan": "Kritis",
  "is_prioritas": true
}
```

#### CURL

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

### G. DELETE `/laporans/{id}`

#### CURL

```bash
curl -X DELETE "$BASE_URL/laporans/10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Laporan berhasil dihapus",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### H. GET `/laporans/{id}/riwayat`

#### CURL

```bash
curl -X GET "$BASE_URL/laporans/10/riwayat" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

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
      "waktu_tindakan": "2026-03-31 10:00:00"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) Referensi yang Dapat Diakses Warga

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
- seluruh endpoint public wilayah
- seluruh endpoint public BMKG

## 4) Akses Operasional untuk Warga (Scoped)

- `GET /tindak-lanjut` -> boleh, tapi hanya data dari laporan milik warga tersebut.
- `GET /tindak-lanjut/{id}` -> boleh jika tindak lanjut terkait laporan miliknya, selain itu `403`.
- `GET /riwayat-tindakan` -> boleh, tapi hanya data dari laporan milik warga tersebut.
- `GET /riwayat-tindakan/{id}` -> boleh jika terkait laporan miliknya, selain itu `403`.
- `GET /monitoring/*` -> tetap ditolak untuk warga.

### A. GET `/tindak-lanjut`

#### CURL

```bash
curl -X GET "$BASE_URL/tindak-lanjut?per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data tindak lanjut berhasil diambil",
  "data": [
    {
      "id_tindaklanjut": 7,
      "laporan_id": 10,
      "id_petugas": 4,
      "status": "Menuju Lokasi"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1,
      "from": 1,
      "to": 1
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### B. GET `/tindak-lanjut/{id}`

#### CURL

```bash
curl -X GET "$BASE_URL/tindak-lanjut/7" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data tindak lanjut berhasil diambil",
  "data": {
    "id_tindaklanjut": 7,
    "laporan_id": 10,
    "id_petugas": 4,
    "status": "Menuju Lokasi"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

#### 403 (Jika bukan milik warga)

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk melihat tindak lanjut ini",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### C. GET `/riwayat-tindakan`

#### CURL

```bash
curl -X GET "$BASE_URL/riwayat-tindakan?per_page=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data riwayat tindakan berhasil diambil",
  "data": [
    {
      "id": 21,
      "tindaklanjut_id": 7,
      "id_petugas": 4,
      "keterangan": "Evakuasi tahap awal",
      "waktu_tindakan": "2026-03-31T10:00:00.000000Z"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1,
      "from": 1,
      "to": 1
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### D. GET `/riwayat-tindakan/{id}`

#### CURL

```bash
curl -X GET "$BASE_URL/riwayat-tindakan/21" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data riwayat tindakan berhasil diambil",
  "data": {
    "id": 21,
    "tindaklanjut_id": 7,
    "id_petugas": 4,
    "keterangan": "Evakuasi tahap awal",
    "waktu_tindakan": "2026-03-31T10:00:00.000000Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

#### 403 (Jika bukan milik warga)

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk melihat riwayat tindakan ini",
  "code": "INSUFFICIENT_PERMISSIONS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### E. GET `/warga/laporans/{id}/detail-lengkap`

Endpoint agregasi baru untuk kebutuhan interface warga: satu call berisi `detail_laporan`, `tindak_lanjut`, dan `riwayat_tindakan`.

#### CURL

```bash
curl -X GET "$BASE_URL/warga/laporans/10/detail-lengkap" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Detail laporan warga berhasil diambil",
  "data": {
    "detail_laporan": {
      "id": 10,
      "id_pelapor": 5,
      "judul_laporan": "Banjir RT 03",
      "status": "Diproses"
    },
    "tindak_lanjut": [
      {
        "id_tindaklanjut": 7,
        "laporan_id": 10,
        "status": "Menuju Lokasi"
      }
    ],
    "riwayat_tindakan": [
      {
        "id": 21,
        "tindaklanjut_id": 7,
        "keterangan": "Evakuasi tahap awal"
      }
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) Matriks Status Endpoint Warga (Ringkas)

- `GET /auth/me` -> `200`, `401`
- `POST /auth/refresh` -> `200`, `401`
- `POST /auth/logout` -> `200`, `401`
- `GET /users/profile` -> `200`, `401`
- `PUT /users/profile` -> `200`, `401`, `422`
- `GET /check-token` -> `200`, `401`
- `GET /laporans` -> `200`
- `GET /laporans/pelapor/{pelaporId}` -> `200`, `403`
- `POST /laporans` -> `201`, `401`, `422`, `500`
- `GET /laporans/{id}` -> `200`, `404`
- `PUT /laporans/{id}` -> `200`, `403`, `422`, `404`
- `DELETE /laporans/{id}` -> `200`, `403`, `404`
- `GET /laporans/statistics` -> `200`, `500`
- `GET /laporans/{id}/riwayat` -> `200`, `404`
- `GET /tindak-lanjut` -> `200`
- `GET /tindak-lanjut/{id}` -> `200`, `403`, `404`
- `GET /riwayat-tindakan` -> `200`
- `GET /riwayat-tindakan/{id}` -> `200`, `403`, `404`
- `GET /warga/laporans/{id}/detail-lengkap` -> `200`, `403`, `404`
