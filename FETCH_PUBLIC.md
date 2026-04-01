# FETCH Public Endpoints

Dokumen ini khusus endpoint publik (tanpa JWT) pada API `v1`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
```

Header minimum:

- `Accept: application/json`
- `Content-Type: application/json` (jika body JSON)

## 1) Register

Endpoint:

- `POST /auth/register`

Catatan penting:

- Endpoint publik register sekarang hanya untuk role `Warga`
- Field `role` opsional, jika tidak diisi akan otomatis di-set `Warga`
- Jika mengirim `role` selain `Warga` akan kena `422 VALIDATION_ERROR`

### Request Body

```json
{
  "nama": "Warga Baru",
  "username": "warga_baru",
  "email": "warga@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "Warga",
  "no_telepon": "081234567890",
  "alamat": "RT 03 RW 02",
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
    "no_telepon":"081234567890",
    "alamat":"RT 03 RW 02",
    "id_desa":1
  }'
```

### 201 Response (Sukses)

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
    "no_telepon": "081234567890",
    "alamat": "RT 03 RW 02",
    "id_desa": 1,
    "created_at": "2026-04-01T08:15:01.000000Z"
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 422 Response (Validasi)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "role": [
      "Role tidak valid"
    ]
  },
  "details": {
    "role": [
      "Role tidak valid"
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 429 Response (Rate Limit)

```json
{
  "success": false,
  "message": "Terlalu banyak permintaan",
  "code": "RATE_LIMITED",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 2) Login

Endpoint:

- `POST /auth/login`

Catatan:

- Field utama request adalah `username`
- Nilai `username` bisa diisi username atau email
- Alias `email` juga diterima (akan dimap ke `username` oleh request class)

### Request Body

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

### 200 Response (Sukses)

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
    "token": "<jwt_token>",
    "token_type": "Bearer",
    "expires_in": 3600
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 401 Response

```json
{
  "success": false,
  "message": "Username/email atau password salah",
  "code": "INVALID_CREDENTIALS",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 3) Roles Reference

Endpoint:

- `GET /auth/roles`

### CURL

```bash
curl -X GET "$BASE_URL/auth/roles" \
  -H "Accept: application/json"
```

### 200 Response

```json
{
  "success": true,
  "message": "Daftar role tersedia",
  "data": {
    "Admin": "Administrator",
    "PetugasBPBD": "Petugas BPBD",
    "OperatorDesa": "Operator Desa",
    "Warga": "Warga"
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 4) Wilayah Public

Endpoint publik:

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

### Contoh CURL

```bash
curl -X GET "$BASE_URL/wilayah?jenis=desa&per_page=20" \
  -H "Accept: application/json"
```

## 5) BMKG Public

Endpoint publik BMKG:

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=...`
- `GET /bmkg/peringatan-dini-cuaca`

Untuk contoh payload lengkap BMKG lihat `FETCH_BMKG.md`.

## Status Matrix (Public)

- `POST /auth/register` -> `201`, `422`, `429`
- `POST /auth/login` -> `200`, `401`, `422`, `429`
- `GET /auth/roles` -> `200`
- `GET /wilayah*` -> `200`, `400`, `404`
- `GET /bmkg*` public -> `200`, `404`, `422`, `500`
