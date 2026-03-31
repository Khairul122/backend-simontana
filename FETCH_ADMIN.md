# FETCH Role: Admin (Lengkap)

Dokumen ini adalah panduan paling lengkap untuk role `Admin`, mencakup Auth utilitas, Users, Laporan, Operasional, Master, Wilayah, dan BMKG.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_admin"
```

## 1) Check Token

### CURL

```bash
curl -X GET "$BASE_URL/check-token" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Token valid",
  "data": {
    "user_id": 1,
    "user_role": "Admin",
    "user_name": "Admin Simonta"
  }
}
```

## 2) Users

Endpoint:

- `GET /users`
- `POST /users`
- `GET /users/statistics`
- `GET /users/{id}`
- `PUT /users/{id}`
- `DELETE /users/{id}`

### A. POST `/users`

#### Full Request Body

```json
{
  "nama": "Operator Desa A",
  "username": "operator_a",
  "email": "operator_a@example.com",
  "password": "password123",
  "role": "OperatorDesa",
  "no_telepon": "081234567890",
  "alamat": "Kantor Desa A",
  "id_desa": 1
}
```

#### CURL

```bash
curl -X POST "$BASE_URL/users" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama":"Operator Desa A",
    "username":"operator_a",
    "email":"operator_a@example.com",
    "password":"password123",
    "role":"OperatorDesa",
    "no_telepon":"081234567890",
    "alamat":"Kantor Desa A",
    "id_desa":1
  }'
```

#### 201 Full Response Body

```json
{
  "success": true,
  "message": "Pengguna berhasil ditambahkan",
  "data": {
    "id": 22,
    "nama": "Operator Desa A",
    "username": "operator_a",
    "email": "operator_a@example.com",
    "role": "OperatorDesa",
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

### B. GET `/users?per_page=20&search=operator`

#### CURL

```bash
curl -X GET "$BASE_URL/users?per_page=20&search=operator" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body (Paginated)

```json
{
  "success": true,
  "message": "Data pengguna berhasil diambil",
  "data": [
    {
      "id": 22,
      "nama": "Operator Desa A",
      "username": "operator_a",
      "role": "OperatorDesa"
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1,
      "from": 1,
      "to": 1
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### C. GET `/users/statistics`

#### CURL

```bash
curl -X GET "$BASE_URL/users/statistics" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Statistik pengguna berhasil diambil",
  "data": {
    "total_users": 120,
    "by_role": [
      {
        "role": "Warga",
        "total": 80
      },
      {
        "role": "OperatorDesa",
        "total": 20
      }
    ],
    "recent_users": [],
    "users_this_month": 9
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) Laporan

Endpoint:

- `GET /laporans`
- `GET /laporans/pelapor/{pelaporId}`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`

### A. GET `/laporans?limit=15`

#### CURL

```bash
curl -X GET "$BASE_URL/laporans?limit=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body (Nested + Pagination)

```json
{
  "success": true,
  "message": "Data laporan berhasil diambil",
  "data": [
    {
      "id": 1,
      "id_pelapor": 5,
      "id_kategori_bencana": 1,
      "id_desa": 1,
      "judul_laporan": "Banjir RT 03",
      "deskripsi": "Air setinggi lutut",
      "tingkat_keparahan": "Tinggi",
      "status": "Diproses",
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
      "last_page": 2,
      "per_page": 15,
      "total": 26,
      "from": 1,
      "to": 15
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### B. GET `/laporans/pelapor/{pelaporId}`

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
      "id": 1,
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

### C. POST `/laporans` (Multipart + Attachment)

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
  -F "alamat=Pariaman" \
  -F "jumlah_korban=0" \
  -F "jumlah_rumah_rusak=0" \
  -F "is_prioritas=true" \
  -F "foto_bukti_1=@./sample1.png" \
  -F "foto_bukti_2=@./sample2.png" \
  -F "foto_bukti_3=@./sample3.png" \
  -F "video_bukti=@./sample.mp4"
```

### D. GET `/laporans/statistics?period=monthly`

#### CURL

```bash
curl -X GET "$BASE_URL/laporans/statistics?period=monthly" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Statistik laporan berhasil diambil",
  "data": {
    "total_laporan": 120,
    "laporan_perlu_verifikasi": 14,
    "laporan_ditindak": 42,
    "laporan_selesai": 55,
    "laporan_ditolak": 9,
    "laporan_baru": 14,
    "laporan_ditangani": 42,
    "weekly_stats": {
      "mon": 1,
      "tue": 3,
      "wed": 0,
      "thu": 2,
      "fri": 5,
      "sat": 1,
      "sun": 2
    },
    "categories_stats": {
      "Banjir": 33,
      "Longsor": 12
    },
    "monthly_trend": {
      "2026-01": 14,
      "2026-02": 18,
      "2026-03": 9
    },
    "top_pengguna": [
      {
        "pengguna_name": "Andi Warga",
        "laporan_count": 8
      }
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 4) Workflow, Operasional, Master, dan Wilayah

### Workflow Laporan

- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

### Operasional

- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`

### Kategori Bencana

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
- `POST /kategori-bencana`
- `PUT/PATCH /kategori-bencana/{id}`
- `DELETE /kategori-bencana/{id}`

### Wilayah

- Public read: `GET /wilayah*`
- Admin CRUD: `POST/PUT/DELETE /wilayah` + endpoint level (`/wilayah/provinsi`, `/wilayah/kabupaten`, `/wilayah/kecamatan`, `/wilayah/desa`)

### BMKG

- Public: `GET /bmkg/gempa/*`, `GET /bmkg/prakiraan-cuaca`, `GET /bmkg/peringatan-dini-cuaca`
- Protected: `GET /bmkg`, `GET /bmkg/cache/status`, `POST /bmkg/cache/clear`

## 5) Full Error Body Contoh

### 400

```json
{
  "success": false,
  "message": "Parameter jenis wajib disertakan",
  "code": "BAD_REQUEST",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404

```json
{
  "success": false,
  "message": "Pengguna tidak ditemukan",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "nama": [
      "The nama field is required."
    ]
  },
  "details": {
    "nama": [
      "The nama field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 500

```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server",
  "code": "INTERNAL_SERVER_ERROR",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 6) Matriks Status Endpoint Admin

- Auth/session: `GET /auth/me`, `POST /auth/refresh`, `POST /auth/logout`, `GET /auth/roles`, `GET /check-token` -> `200`, `401`
- Users: `GET/POST/GET{id}/PUT{id}/DELETE{id}` + `GET /users/statistics` -> `200`, `201`, `403`, `404`, `422`
- Laporan: `GET/POST/GET{id}/PUT{id}/DELETE{id}` + `GET /laporans/pelapor/{pelaporId}` + `GET /laporans/statistics` -> `200`, `201`, `401`, `403`, `404`, `422`, `500`
- Workflow: `POST /laporans/{id}/verifikasi`, `POST /laporans/{id}/proses`, `GET /laporans/{id}/riwayat` -> `200`, `403`, `404`, `422`
- Monitoring/TindakLanjut/RiwayatTindakan CRUD -> `200`, `201`, `403`, `404`, `422`
- Kategori Bencana CRUD -> `200`, `201`, `400`, `403`, `404`, `422`
- Wilayah read + CRUD -> `200`, `201`, `400`, `403`, `404`, `422`
- BMKG public/protected -> `200`, `401`, `404`, `422`, `500`
