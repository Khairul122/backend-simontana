# FETCH Role: Admin (Full Response)

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_admin"
```

## 1) Check Token

```bash
curl -X GET "$BASE_URL/check-token" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body

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

## 2) User Management

Endpoint:
- `GET /users`
- `POST /users`
- `GET /users/statistics`
- `GET /users/{id}`
- `PUT /users/{id}`
- `DELETE /users/{id}`

Contoh `POST /users`:

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
    "id_desa":1
  }'
```

### 201 Response Body

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

Contoh `GET /users?per_page=20`:

```bash
curl -X GET "$BASE_URL/users?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (Paginated)

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

## 3) Laporan Management

Endpoint:
- `GET /laporans`
- `GET /laporans/pelapor/{pelaporId}`
- `POST /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}`
- `DELETE /laporans/{id}`
- `GET /laporans/statistics`

Contoh `GET /laporans?limit=15`:

```bash
curl -X GET "$BASE_URL/laporans?limit=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

Contoh `GET /laporans/pelapor/{pelaporId}`:

```bash
curl -X GET "$BASE_URL/laporans/pelapor/5?status=Draft&limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (By Pelapor)

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

### 200 Response Body (Nested + Pagination)

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

Contoh `GET /laporans/statistics?period=monthly`:

```bash
curl -X GET "$BASE_URL/laporans/statistics?period=monthly" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (Statistics)

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

## 4) Operasional dan Master

Endpoint:
- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`
- `GET/POST/PUT/PATCH/DELETE /kategori-bencana`
- `GET/POST/PUT/DELETE /wilayah` dan turunannya

### 400 Response Body

```json
{
  "success": false,
  "message": "Parameter jenis wajib disertakan",
  "code": "BAD_REQUEST",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404 Response Body

```json
{
  "success": false,
  "message": "Pengguna tidak ditemukan",
  "code": "RESOURCE_NOT_FOUND",
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

### 500 Response Body

```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server",
  "code": "INTERNAL_SERVER_ERROR",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) Matriks Status Semua Endpoint Admin

- `GET /auth/me`, `GET /users/profile`, `GET /check-token`
  - `200`, `401`

- `POST /auth/refresh`, `POST /auth/logout`
  - `200`, `401`

- `GET /auth/roles`
  - `200`

- `GET /users`
  - `200`

- `POST /users`
  - `201`, `422`, `403`

- `GET /users/statistics`
  - `200`, `403`

- `GET /users/{id}`
  - `200`, `404`, `403`

- `PUT /users/{id}`
  - `200`, `404`, `422`, `403`

- `DELETE /users/{id}`
  - `200`, `404`, `403`

- `GET /laporans`
  - `200`

- `POST /laporans`
  - `201`, `422`, `401`

- `GET /laporans/{id}`
  - `200`, `404`

- `PUT /laporans/{id}`
  - `200`, `403`, `422`, `404`

- `DELETE /laporans/{id}`
  - `200`, `403`, `404`

- `GET /laporans/statistics`
  - `200`, `500`

- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
  - `200`, `403`, `404`, `422`

- `GET /laporans/{id}/riwayat`
  - `200`, `404`

- `GET/POST/PUT/DELETE /monitoring`
  - `GET`: `200`
  - `POST`: `201`, `422`, `403`
  - `PUT`: `200`, `404`, `422`, `403`
  - `DELETE`: `200`, `404`, `403`

- `GET/POST/PUT/DELETE /tindak-lanjut`
  - `GET`: `200`
  - `POST`: `201`, `422`, `403`
  - `PUT`: `200`, `404`, `422`, `403`
  - `DELETE`: `200`, `404`, `403`

- `GET/POST/PUT/DELETE /riwayat-tindakan`
  - `GET`: `200`
  - `POST`: `201`, `422`, `403`
  - `PUT`: `200`, `404`, `422`, `403`
  - `DELETE`: `200`, `404`, `403`

- `GET /kategori-bencana`
  - `200`

- `GET /kategori-bencana/{id}`
  - `200`, `404`

- `POST /kategori-bencana`
  - `201`, `422`, `403`

- `PUT/PATCH /kategori-bencana/{id}`
  - `200`, `404`, `422`, `403`

- `DELETE /kategori-bencana/{id}`
  - `200`, `404`, `400`, `403`

- `GET /wilayah`, `GET /wilayah/{id}`, `GET /wilayah/provinsi*`, `GET /wilayah/kabupaten/*`, `GET /wilayah/kecamatan/*`, `GET /wilayah/desa/*`, `GET /wilayah/detail/*`, `GET /wilayah/hierarchy/*`, `GET /wilayah/search`
  - `200`, `400`, `404`

- `POST/PUT/DELETE /wilayah` dan endpoint CRUD level wilayah
  - `201`/`200`, `400`, `404`, `422`, `403`

- `GET /bmkg/gempa/*`, `GET /bmkg/peringatan-tsunami`, `GET /bmkg/prakiraan-cuaca`
  - `200`, `404`, `422`, `500`

- `GET /bmkg`, `GET /bmkg/cache/status`, `POST /bmkg/cache/clear`
  - `200`, `500`, `401`
