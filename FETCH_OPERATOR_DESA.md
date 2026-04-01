# FETCH Role: OperatorDesa

Panduan endpoint utama untuk role `OperatorDesa`: workflow laporan + operasional lapangan.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_operator_desa"
```

## 1) Workflow Laporan

Endpoint:

- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

### POST /laporans/{id}/verifikasi

Body:

```json
{
  "status": "Diverifikasi",
  "catatan_verifikasi": "Data valid dari lapangan"
}
```

```bash
curl -X POST "$BASE_URL/laporans/1/verifikasi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "status":"Diverifikasi",
    "catatan_verifikasi":"Data valid dari lapangan"
  }'
```

Status valid verifikasi:

- `Diverifikasi`
- `Ditolak`

Jika transisi status tidak valid:

```json
{
  "success": false,
  "message": "Transisi status tidak valid: Selesai -> Diproses",
  "code": "INVALID_STATUS_TRANSITION",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### POST /laporans/{id}/proses

Body:

```json
{
  "status": "Diproses"
}
```

Nilai `status` yang diizinkan pada endpoint ini:

- `Diproses`
- `Selesai`

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diproses"}'
```

### GET /laporans/{id}/riwayat

```bash
curl -X GET "$BASE_URL/laporans/1/riwayat" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 2) Monitoring

Endpoint:

- `GET /monitoring`
- `POST /monitoring`
- `GET /monitoring/{id}`
- `PUT /monitoring/{id}`
- `DELETE /monitoring/{id}`

### GET /monitoring?per_page=20

```bash
curl -X GET "$BASE_URL/monitoring?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### POST /monitoring

Body:

```json
{
  "id_laporan": 1,
  "id_operator": 2,
  "waktu_monitoring": "2026-04-01 09:00:00",
  "hasil_monitoring": "Debit air menurun",
  "koordinat_gps": "-6.2000,106.8000"
}
```

```bash
curl -X POST "$BASE_URL/monitoring" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "id_laporan":1,
    "id_operator":2,
    "waktu_monitoring":"2026-04-01 09:00:00",
    "hasil_monitoring":"Debit air menurun",
    "koordinat_gps":"-6.2000,106.8000"
  }'
```

Catatan policy:

- Non-admin tidak bisa spoof `id_operator` user lain
- Update monitoring milik operator lain akan ditolak (`403`)

## 3) Tindak Lanjut

Endpoint:

- `GET /tindak-lanjut`
- `POST /tindak-lanjut`
- `GET /tindak-lanjut/{id}`
- `PUT /tindak-lanjut/{id}`
- `DELETE /tindak-lanjut/{id}`

### POST /tindak-lanjut

Body:

```json
{
  "laporan_id": 1,
  "id_petugas": 2,
  "tanggal_tanggapan": "2026-04-01 10:00:00",
  "status": "Menuju Lokasi"
}
```

Status tindak lanjut yang diizinkan:

- `Menuju Lokasi`
- `Selesai`

```bash
curl -X POST "$BASE_URL/tindak-lanjut" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "laporan_id":1,
    "id_petugas":2,
    "tanggal_tanggapan":"2026-04-01 10:00:00",
    "status":"Menuju Lokasi"
  }'
```

## 4) Riwayat Tindakan

Endpoint:

- `GET /riwayat-tindakan`
- `POST /riwayat-tindakan`
- `GET /riwayat-tindakan/{id}`
- `PUT /riwayat-tindakan/{id}`
- `DELETE /riwayat-tindakan/{id}`

### POST /riwayat-tindakan

Body:

```json
{
  "tindaklanjut_id": 7,
  "id_petugas": 2,
  "keterangan": "Evakuasi tahap awal",
  "waktu_tindakan": "2026-04-01 10:30:00"
}
```

```bash
curl -X POST "$BASE_URL/riwayat-tindakan" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "tindaklanjut_id":7,
    "id_petugas":2,
    "keterangan":"Evakuasi tahap awal",
    "waktu_tindakan":"2026-04-01 10:30:00"
  }'
```

## 5) Laporan Read/Update Tambahan

OperatorDesa juga dapat mengakses endpoint laporan protected:

- `GET /laporans`
- `GET /laporans/{id}`
- `PUT /laporans/{id}` (sesuai policy)
- `GET /laporans/statistics`

## 6) Error Contract Umum

### 401

```json
{
  "success": false,
  "message": "Token tidak valid",
  "code": "UNAUTHORIZED",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

### 403

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk mengubah monitoring ini",
  "code": "INSUFFICIENT_PERMISSIONS",
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
    "id_laporan": [
      "The selected id laporan is invalid."
    ]
  },
  "details": {
    "id_laporan": [
      "The selected id laporan is invalid."
    ]
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```
