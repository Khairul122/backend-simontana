# FETCH Public Endpoints

Dokumen ini berisi endpoint yang bisa diakses tanpa JWT.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
```

## 1) Auth Public

### POST `/auth/register`

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
    "role":"Warga"
  }'
```

Respon: `201`, `422`, `429`, `500`

### POST `/auth/login`

```bash
curl -X POST "$BASE_URL/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'
```

Respon: `200`, `401`, `422`, `429`

### GET `/auth/roles`

```bash
curl -X GET "$BASE_URL/auth/roles" -H "Accept: application/json"
```

Respon: `200`

## 2) BMKG Public

- GET `/bmkg/gempa/terbaru`
- GET `/bmkg/gempa/terkini`
- GET `/bmkg/gempa/dirasakan`
- GET `/bmkg/peringatan-tsunami`
- GET `/bmkg/prakiraan-cuaca?wilayah_id=3171`

Contoh:

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terbaru" -H "Accept: application/json"
curl -X GET "$BASE_URL/bmkg/prakiraan-cuaca?wilayah_id=3171" -H "Accept: application/json"
```

Respon: `200`, `404`, `422`, `500`

## 3) Wilayah Public

- GET `/wilayah`
- GET `/wilayah/{id}`
- GET `/wilayah/provinsi`
- GET `/wilayah/provinsi/{id}`
- GET `/wilayah/kabupaten/{provinsi_id}`
- GET `/wilayah/kecamatan/{kabupaten_id}`
- GET `/wilayah/desa/{kecamatan_id}`
- GET `/wilayah/detail/{desa_id}`
- GET `/wilayah/hierarchy/{desa_id}`
- GET `/wilayah/search?q=jakarta`

Contoh:

```bash
curl -X GET "$BASE_URL/wilayah/provinsi" -H "Accept: application/json"
curl -X GET "$BASE_URL/wilayah/search?q=jakarta" -H "Accept: application/json"
```

Respon: `200`, `400`, `404`

## 4) Swagger Utility

- GET `/documentation`
- GET `/oauth2-callback`
