# FETCH Role: Admin

Panduan lengkap endpoint untuk role `Admin` mencakup users, laporan, workflow, operasional, master data, wilayah, dan BMKG.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_admin"
```

## 1) Check Token

Endpoint:

- `GET /check-token`

```bash
curl -X GET "$BASE_URL/check-token" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 2) Users Management

Endpoint:

- `GET /users`
- `POST /users`
- `GET /users/statistics`
- `GET /users/{id}`
- `PUT /users/{id}`
- `DELETE /users/{id}`

### POST /users

Request body:

```json
{
  "nama": "Operator Desa A",
  "username": "operator_a",
  "email": "operator_a@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "OperatorDesa",
  "no_telepon": "081234567890",
  "alamat": "Kantor Desa A",
  "id_desa": 1
}
```

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
    "password_confirmation":"password123",
    "role":"OperatorDesa",
    "no_telepon":"081234567890",
    "alamat":"Kantor Desa A",
    "id_desa":1
  }'
```

### GET /users

```bash
curl -X GET "$BASE_URL/users?per_page=20&search=operator&role=OperatorDesa" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### PUT /users/{id}

Request body:

```json
{
  "nama": "Operator Desa A Update",
  "email": "operator_a_update@example.com",
  "no_telepon": "081299999999",
  "alamat": "Alamat baru",
  "id_desa": 2
}
```

```bash
curl -X PUT "$BASE_URL/users/22" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama":"Operator Desa A Update",
    "email":"operator_a_update@example.com",
    "no_telepon":"081299999999",
    "alamat":"Alamat baru",
    "id_desa":2
  }'
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

### GET /laporans

Contoh filter lengkap:

```bash
curl -X GET "$BASE_URL/laporans?status=Diproses&kategori_id=1&id_desa=1&order_by=created_at&order_direction=desc&limit=15" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### GET /laporans/statistics

Filter yang tersedia:

- `period`: `all|weekly|monthly|yearly`
- `id_desa`
- `id_pelapor`

```bash
curl -X GET "$BASE_URL/laporans/statistics?period=monthly&id_desa=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 4) Workflow Laporan

Endpoint:

- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

### POST /laporans/{id}/verifikasi

```bash
curl -X POST "$BASE_URL/laporans/1/verifikasi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "status":"Diverifikasi",
    "catatan_verifikasi":"Verifikasi valid"
  }'
```

### POST /laporans/{id}/proses

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Selesai"}'
```

## 5) Operasional

Endpoint:

- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`

Contoh membuat monitoring:

```bash
curl -X POST "$BASE_URL/monitoring" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "id_laporan":1,
    "id_operator":2,
    "waktu_monitoring":"2026-04-01 09:00:00",
    "hasil_monitoring":"Kondisi membaik",
    "koordinat_gps":"-6.2000,106.8000"
  }'
```

## 6) Kategori Bencana

Endpoint:

- `GET /kategori-bencana`
- `GET /kategori-bencana/{id}`
- `POST /kategori-bencana`
- `PUT/PATCH /kategori-bencana/{id}`
- `DELETE /kategori-bencana/{id}`

### POST /kategori-bencana

```bash
curl -X POST "$BASE_URL/kategori-bencana" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama_kategori":"Banjir Bandang",
    "deskripsi":"Genangan deras akibat hujan ekstrem",
    "icon":"flood"
  }'
```

## 7) Wilayah

Endpoint admin write:

- `POST /wilayah`
- `PUT /wilayah/{id}`
- `DELETE /wilayah/{id}`
- level endpoint: `/wilayah/provinsi`, `/wilayah/kabupaten`, `/wilayah/kecamatan`, `/wilayah/desa`

### POST /wilayah (generic)

```bash
curl -X POST "$BASE_URL/wilayah" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "jenis":"kabupaten",
    "nama":"Kabupaten Contoh",
    "id_parent":1
  }'
```

### POST /wilayah/desa (level endpoint)

```bash
curl -X POST "$BASE_URL/wilayah/desa" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "nama":"Desa Contoh",
    "id_kecamatan":10
  }'
```

## 8) BMKG

Protected endpoint untuk admin:

- `GET /bmkg`
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

```bash
curl -X GET "$BASE_URL/bmkg/cache/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 9) Error Contract Umum

### 400

```json
{
  "success": false,
  "message": "Parameter jenis wajib disertakan",
  "code": "BAD_REQUEST",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 404

```json
{
  "success": false,
  "message": "Pengguna tidak ditemukan",
  "code": "RESOURCE_NOT_FOUND",
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
    "nama": [
      "The nama field is required."
    ]
  },
  "details": {
    "nama": [
      "The nama field is required."
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```
