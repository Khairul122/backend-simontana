# FETCH Public Endpoints (Full Response)

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
```

## 1) POST `/auth/register`

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

### 201 Response Body

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
    "no_telepon": null,
    "alamat": null,
    "id_desa": 1,
    "created_at": "2026-03-20T09:10:11.000000Z"
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

### 429 Response Body

```json
{
  "success": false,
  "message": "Terlalu banyak permintaan",
  "code": "RATE_LIMITED",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) POST `/auth/login`

```bash
curl -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

### 200 Response Body

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
      "desa": null
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 401 Response Body

```json
{
  "success": false,
  "message": "Username/email atau password salah",
  "code": "INVALID_CREDENTIALS",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) GET `/auth/roles`

```bash
curl -X GET "$BASE_URL/auth/roles" -H "Accept: application/json"
```

### 200 Response Body

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

Endpoint:
- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/peringatan-tsunami`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=3171`

Contoh `GET /bmkg/gempa/terbaru`:

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terbaru" -H "Accept: application/json"
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Data gempa terbaru berhasil diambil",
  "data": {
    "Tanggal": "20 Mar 2026",
    "Jam": "08:10:01 WIB",
    "Magnitude": "5.2",
    "Kedalaman": "10 km",
    "Wilayah": "Selatan Jawa",
    "Potensi": "Tidak berpotensi tsunami"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) Wilayah Public

Endpoint:
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

Contoh `GET /wilayah?jenis=desa&per_page=20`:

```bash
curl -X GET "$BASE_URL/wilayah?jenis=desa&per_page=20" -H "Accept: application/json"
```

### 200 Response Body (Paginated)

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

### 400 Response Body

```json
{
  "success": false,
  "message": "Parameter pencarian (q) wajib disertakan",
  "code": "BAD_REQUEST",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404 Response Body

```json
{
  "success": false,
  "message": "Desa tidak ditemukan",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 6) Matriks Status Semua Endpoint Public

- `POST /auth/register`
  - `201` -> lihat section `201 Response Body` register
  - `422` -> lihat section `422 Response Body` register
  - `429` -> lihat section `429 Response Body` register
  - `500` -> gunakan body standar:

```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server",
  "code": "INTERNAL_SERVER_ERROR",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

- `POST /auth/login`
  - `200` -> lihat section `200 Response Body` login
  - `401` -> lihat section `401 Response Body` login
  - `422` -> body validasi sama seperti section validasi register
  - `429` -> body rate limit sama seperti section `429 Response Body`

- `GET /auth/roles`
  - `200` -> lihat section `200 Response Body` roles

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/peringatan-tsunami`
  - `200` -> contoh body BMKG section `200 Response Body`
  - `404` ->

```json
{
  "success": false,
  "message": "Data tidak tersedia",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `500` -> body internal error standar

- `GET /bmkg/prakiraan-cuaca?wilayah_id=3171`
  - `200` -> contoh body BMKG section `200 Response Body`
  - `404` -> body `Data tidak tersedia` (lihat di atas)
  - `422` ->

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "wilayah_id": [
      "The wilayah id field is required."
    ]
  },
  "details": {
    "wilayah_id": [
      "The wilayah id field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

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
  - `200` -> lihat section `200 Response Body (Paginated)` (shape list) atau object sesuai endpoint detail
  - `400` -> lihat section `400 Response Body`
  - `404` -> lihat section `404 Response Body`
