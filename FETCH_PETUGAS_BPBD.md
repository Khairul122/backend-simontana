# FETCH Role: PetugasBPBD

Panduan endpoint utama untuk role `PetugasBPBD`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_petugas_bpbd"
```

## 1) Workflow Laporan

Endpoint:

- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

### Contoh POST /laporans/{id}/proses

Request body:

```json
{
  "status": "Diproses"
}
```

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diproses"}'
```

Jika transisi invalid:

```json
{
  "success": false,
  "message": "Transisi status tidak valid: Ditolak -> Diproses",
  "code": "INVALID_STATUS_TRANSITION",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 2) Operasional Lapangan

Endpoint:

- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`

### Contoh GET /tindak-lanjut

```bash
curl -X GET "$BASE_URL/tindak-lanjut?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Contoh POST /riwayat-tindakan

Body:

```json
{
  "tindaklanjut_id": 7,
  "id_petugas": 3,
  "keterangan": "Distribusi logistik tahap 1",
  "waktu_tindakan": "2026-04-01 12:00:00"
}
```

```bash
curl -X POST "$BASE_URL/riwayat-tindakan" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "tindaklanjut_id":7,
    "id_petugas":3,
    "keterangan":"Distribusi logistik tahap 1",
    "waktu_tindakan":"2026-04-01 12:00:00"
  }'
```

## 3) Laporan Read/Update

PetugasBPBD juga dapat:

- `GET /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}` (sesuai policy)
- `GET /laporans/statistics`

## 4) BMKG Protected

Endpoint:

- `GET /bmkg`
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

### Contoh GET /bmkg

```bash
curl -X GET "$BASE_URL/bmkg" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 5) Error Contract Umum

### 403

```json
{
  "success": false,
  "message": "Akses ditolak",
  "code": "FORBIDDEN",
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
    "waktu_tindakan": [
      "The waktu tindakan field is required."
    ]
  },
  "details": {
    "waktu_tindakan": [
      "The waktu tindakan field is required."
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```
