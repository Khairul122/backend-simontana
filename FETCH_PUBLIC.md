# FETCH Public Endpoints (Lengkap)

Dokumen ini membahas endpoint public (tanpa JWT) yang bisa diakses langsung oleh client.
Semua contoh memakai base URL `v1`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
```

Header minimum:

- `Accept: application/json`
- `Content-Type: application/json` (khusus body JSON)

## 1) POST `/auth/register`

Mendaftarkan user baru.

### Full Request Body (JSON)

```json
{
  "nama": "Warga Baru",
  "username": "warga_baru",
  "email": "warga@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "Warga",
  "id_desa": 1
}
```

### CURL

```bash
curl -X POST "$BASE_URL/auth/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "nama":"Warga Baru",
    "username":"warga_baru",
    "email":"warga@example.com",
    "password":"password123",
    "password_confirmation":"password123",
    "role":"Warga",
    "id_desa":1
  }'
```

### 201 Full Response Body

```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "id": 101,
    "nama": "Warga Baru",
    "username": "warga_baru",
    "email": "warga@example.com",
    "role": "Warga",
    "role_label": "Warga",
    "no_telepon": null,
    "alamat": null,
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
    },
    "created_at": "2026-03-31T10:10:11.000000Z",
    "updated_at": "2026-03-31T10:10:11.000000Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Full Response Body

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password confirmation does not match."
    ]
  },
  "details": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password confirmation does not match."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 429 Full Response Body

```json
{
  "success": false,
  "message": "Terlalu banyak permintaan",
  "code": "RATE_LIMITED",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) POST `/auth/login`

Login menerima `username` (username atau email alias).

### Full Request Body (JSON)

```json
{
  "username": "admin",
  "password": "password123"
}
```

### CURL

```bash
curl -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "nama": "Admin Simonta",
      "username": "admin",
      "email": "admin@simonta.id",
      "role": "Admin",
      "role_label": "Administrator",
      "no_telepon": "081234567890",
      "alamat": "Kantor BPBD",
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
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 401 Full Response Body

```json
{
  "success": false,
  "message": "Username/email atau password salah",
  "code": "INVALID_CREDENTIALS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Full Response Body

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "username": [
      "The username field is required."
    ],
    "password": [
      "The password field is required."
    ]
  },
  "details": {
    "username": [
      "The username field is required."
    ],
    "password": [
      "The password field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) GET `/auth/roles`

Mengambil daftar role yang didukung sistem.

### CURL

```bash
curl -X GET "$BASE_URL/auth/roles" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Daftar role tersedia",
  "data": [
    "Admin",
    "PetugasBPBD",
    "OperatorDesa",
    "Warga"
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 4) BMKG Public

Endpoint public BMKG:

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=...`
- `GET /bmkg/peringatan-dini-cuaca`

Lihat contoh super lengkap di `FETCH_BMKG.md`.

## 5) Wilayah Public

Endpoint public wilayah:

- `GET /wilayah`
- `GET /wilayah?jenis=desa&per_page=20`
- `GET /wilayah/{id}?jenis=desa`
- `GET /wilayah/provinsi`
- `GET /wilayah/provinsi/{id}`
- `GET /wilayah/kabupaten/{provinsi_id}`
- `GET /wilayah/kecamatan/{kabupaten_id}`
- `GET /wilayah/desa/{kecamatan_id}`
- `GET /wilayah/detail/{desa_id}`
- `GET /wilayah/hierarchy/{desa_id}`
- `GET /wilayah/search?q=jakarta`

### Contoh Full Request + CURL: List Desa Paginated

Request query:

```text
GET /wilayah?jenis=desa&per_page=20
```

CURL:

```bash
curl -X GET "$BASE_URL/wilayah?jenis=desa&per_page=20" \
  -H "Accept: application/json"
```

### 200 Full Response Body (Paginated)

```json
{
  "success": true,
  "message": "Data wilayah berhasil diambil",
  "data": [
    {
      "id": 1,
      "nama": "Sukamaju",
      "id_kecamatan": 12,
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
  ],
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

### 400 Full Response Body (Search q kosong)

```json
{
  "success": false,
  "message": "Parameter pencarian (q) wajib disertakan",
  "code": "BAD_REQUEST",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404 Full Response Body

```json
{
  "success": false,
  "message": "Desa tidak ditemukan",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 6) Matriks Status Endpoint Public

- `POST /auth/register` -> `201`, `422`, `429`, `500`
- `POST /auth/login` -> `200`, `401`, `422`, `429`
- `GET /auth/roles` -> `200`
- `GET /bmkg/*` public -> `200`, `404`, `422` (khusus prakiraan), `500`
- `GET /wilayah*` public -> `200`, `400`, `404`

## 7) Catatan Penting

- Endpoint non-versioned (`/api/*`) tidak dipakai; gunakan `/api/v1/*`.
- Untuk call protected (di dokumen role), sertakan `Authorization: Bearer <token>`.
- Jika response tidak sesuai dokumen, cek `request_id` lalu cocokkan dengan log backend.
