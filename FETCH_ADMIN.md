# FETCH Role: Admin

Dokumen ini untuk role `Admin` (akses paling lengkap).

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="token_admin"
```

## 1) Auth dan Profil

- GET `/auth/me`
- POST `/auth/refresh`
- POST `/auth/logout`
- GET `/users/profile`
- PUT `/users/profile`
- GET `/check-token`

## 2) User Management (Admin only)

- GET `/users`
- POST `/users`
- GET `/users/statistics`
- GET `/users/{id}`
- PUT `/users/{id}`
- DELETE `/users/{id}`

Contoh create user:

```bash
curl -X POST "$BASE_URL/users" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama":"Operator Desa A",
    "username":"operator_a",
    "password":"password123",
    "role":"OperatorDesa"
  }'
```

## 3) Laporan Management

- GET `/laporans`
- POST `/laporans`
- GET `/laporans/{id}`
- PUT/PATCH `/laporans/{id}`
- DELETE `/laporans/{id}`
- GET `/laporans/statistics`

## 4) Laporan Workflow

- POST `/laporans/{id}/verifikasi`
- POST `/laporans/{id}/proses`
- GET `/laporans/{id}/riwayat`

Catatan: transisi status tidak valid menghasilkan `422` code `INVALID_STATUS_TRANSITION`.

## 5) Operasional

- Monitoring: GET/POST/GET{id}/PUT/DELETE `/monitoring`
- Tindak lanjut: GET/POST/GET{id}/PUT/DELETE `/tindak-lanjut`
- Riwayat tindakan: GET/POST/GET{id}/PUT/DELETE `/riwayat-tindakan`

## 6) Kategori Bencana (Admin full)

- GET `/kategori-bencana`
- GET `/kategori-bencana/{id}`
- POST `/kategori-bencana`
- PUT `/kategori-bencana/{id}`
- PATCH `/kategori-bencana/{id}`
- DELETE `/kategori-bencana/{id}`

## 7) Wilayah (Admin full)

### Public-like listing

- GET `/wilayah`
- GET `/wilayah/{id}`
- GET `/wilayah/provinsi`
- GET `/wilayah/provinsi/{id}`
- GET `/wilayah/kabupaten/{provinsi_id}`
- GET `/wilayah/kecamatan/{kabupaten_id}`
- GET `/wilayah/desa/{kecamatan_id}`
- GET `/wilayah/detail/{desa_id}`
- GET `/wilayah/hierarchy/{desa_id}`
- GET `/wilayah/search?q=...`

### CRUD umum

- POST `/wilayah`
- PUT `/wilayah/{id}`
- DELETE `/wilayah/{id}`

### CRUD per level

- POST/PUT/DELETE `/wilayah/provinsi`
- POST/PUT/DELETE `/wilayah/kabupaten`
- POST/PUT/DELETE `/wilayah/kecamatan`
- POST/PUT/DELETE `/wilayah/desa`

## 8) BMKG

### Public BMKG

- GET `/bmkg/gempa/terbaru`
- GET `/bmkg/gempa/terkini`
- GET `/bmkg/gempa/dirasakan`
- GET `/bmkg/peringatan-tsunami`
- GET `/bmkg/prakiraan-cuaca?wilayah_id=3171`

### Protected BMKG

- GET `/bmkg`
- GET `/bmkg/cache/status`
- POST `/bmkg/cache/clear`

## 9) Contoh Response Gagal Permission

```json
{
  "success": false,
  "message": "Anda tidak memiliki izin untuk melakukan tindakan ini",
  "code": "INSUFFICIENT_PERMISSIONS"
}
```
