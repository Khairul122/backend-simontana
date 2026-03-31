# FETCH Role: OperatorDesa (Lengkap)

Dokumen ini fokus untuk role `OperatorDesa`: workflow laporan + operasional lapangan.

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

### A. POST `/laporans/{id}/verifikasi`

#### Full Request Body

```json
{
  "status": "Diverifikasi",
  "catatan_verifikasi": "Data valid dari lapangan"
}
```

#### CURL

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

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Laporan berhasil diverifikasi",
  "data": {
    "id": 1,
    "status": "Diverifikasi",
    "id_verifikator": 2,
    "catatan_verifikasi": "Data valid dari lapangan",
    "pelapor": {
      "id": 5,
      "nama": "Andi Warga"
    },
    "desa": {
      "id": 1,
      "nama": "Sukamaju"
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

#### 422 Full Response Body (invalid transition)

```json
{
  "success": false,
  "message": "Transisi status tidak valid: Selesai -> Diproses",
  "code": "INVALID_STATUS_TRANSITION",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### B. POST `/laporans/{id}/proses`

#### Full Request Body

```json
{
  "status": "Diproses"
}
```

#### CURL

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diproses"}'
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Status laporan berhasil diperbarui",
  "data": {
    "id": 1,
    "status": "Diproses",
    "id_penanggung_jawab": 2
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### C. GET `/laporans/{id}/riwayat`

#### CURL

```bash
curl -X GET "$BASE_URL/laporans/1/riwayat" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body

```json
{
  "success": true,
  "message": "Riwayat laporan berhasil diambil",
  "data": [
    {
      "id": 21,
      "tindaklanjut_id": 7,
      "id_petugas": 2,
      "keterangan": "Evakuasi tahap awal",
      "waktu_tindakan": "2026-03-31 10:00:00"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) Monitoring

Endpoint:

- `GET /monitoring`
- `POST /monitoring`
- `GET /monitoring/{id}`
- `PUT /monitoring/{id}`
- `DELETE /monitoring/{id}`

### A. GET `/monitoring?per_page=20`

#### CURL

```bash
curl -X GET "$BASE_URL/monitoring?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

#### 200 Full Response Body (Paginated)

```json
{
  "success": true,
  "message": "Data monitoring berhasil diambil",
  "data": [
    {
      "id_monitoring": 99,
      "id_laporan": 1,
      "id_operator": 2,
      "waktu_monitoring": "2026-03-31 08:00:00",
      "hasil_monitoring": "Kondisi terkendali",
      "koordinat_gps": "-6.2,106.8",
      "operator": {
        "id": 2,
        "nama": "Operator Desa"
      },
      "laporan": {
        "id": 1,
        "judul_laporan": "Banjir RT 03"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 20,
      "total": 34,
      "from": 1,
      "to": 20
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### B. POST `/monitoring`

#### Full Request Body

```json
{
  "id_laporan": 1,
  "id_operator": 2,
  "waktu_monitoring": "2026-03-31 09:00:00",
  "hasil_monitoring": "Debit air menurun",
  "koordinat_gps": "-6.2000,106.8000"
}
```

#### CURL

```bash
curl -X POST "$BASE_URL/monitoring" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "id_laporan":1,
    "id_operator":2,
    "waktu_monitoring":"2026-03-31 09:00:00",
    "hasil_monitoring":"Debit air menurun",
    "koordinat_gps":"-6.2000,106.8000"
  }'
```

#### 201 Full Response Body

```json
{
  "success": true,
  "message": "Monitoring berhasil dibuat",
  "data": {
    "id_monitoring": 100,
    "id_laporan": 1,
    "id_operator": 2,
    "hasil_monitoring": "Debit air menurun"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

#### 422 Full Response Body

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
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) Tindak Lanjut

Endpoint:

- `GET /tindak-lanjut`
- `POST /tindak-lanjut`
- `GET /tindak-lanjut/{id}`
- `PUT /tindak-lanjut/{id}`
- `DELETE /tindak-lanjut/{id}`

### POST `/tindak-lanjut` Full Request + Response

#### Request Body

```json
{
  "laporan_id": 1,
  "id_petugas": 2,
  "tanggal_tanggapan": "2026-03-31 10:00:00",
  "status": "Menuju Lokasi"
}
```

#### CURL

```bash
curl -X POST "$BASE_URL/tindak-lanjut" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "laporan_id":1,
    "id_petugas":2,
    "tanggal_tanggapan":"2026-03-31 10:00:00",
    "status":"Menuju Lokasi"
  }'
```

#### 201 Full Response Body

```json
{
  "success": true,
  "message": "Tindak lanjut berhasil dibuat",
  "data": {
    "id_tindaklanjut": 7,
    "laporan_id": 1,
    "id_petugas": 2,
    "status": "Menuju Lokasi",
    "petugas": {
      "id": 2,
      "nama": "Operator Desa"
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 4) Riwayat Tindakan

Endpoint:

- `GET /riwayat-tindakan`
- `POST /riwayat-tindakan`
- `GET /riwayat-tindakan/{id}`
- `PUT /riwayat-tindakan/{id}`
- `DELETE /riwayat-tindakan/{id}`

### POST `/riwayat-tindakan` Full Request + Response

#### Request Body

```json
{
  "tindaklanjut_id": 7,
  "id_petugas": 2,
  "keterangan": "Evakuasi tahap awal",
  "waktu_tindakan": "2026-03-31 10:30:00"
}
```

#### CURL

```bash
curl -X POST "$BASE_URL/riwayat-tindakan" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "tindaklanjut_id":7,
    "id_petugas":2,
    "keterangan":"Evakuasi tahap awal",
    "waktu_tindakan":"2026-03-31 10:30:00"
  }'
```

#### 201 Full Response Body

```json
{
  "success": true,
  "message": "Riwayat tindakan berhasil dibuat",
  "data": {
    "id": 21,
    "tindaklanjut_id": 7,
    "id_petugas": 2,
    "keterangan": "Evakuasi tahap awal",
    "waktu_tindakan": "2026-03-31 10:30:00"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) Matriks Status OperatorDesa

- `GET /laporans` -> `200`
- `GET /laporans/pelapor/{pelaporId}` -> `200`
- `GET /laporans/{id}` -> `200`, `404`
- `PUT /laporans/{id}` -> `200`, `403`, `422`, `404`
- `GET /laporans/statistics` -> `200`
- `POST /laporans/{id}/verifikasi` -> `200`, `403`, `404`, `422`
- `POST /laporans/{id}/proses` -> `200`, `403`, `404`, `422`
- `GET /laporans/{id}/riwayat` -> `200`, `404`
- `GET/POST/PUT/DELETE /monitoring` -> `200`, `201`, `403`, `404`, `422`
- `GET/POST/PUT/DELETE /tindak-lanjut` -> `200`, `201`, `403`, `404`, `422`
- `GET/POST/PUT/DELETE /riwayat-tindakan` -> `200`, `201`, `403`, `404`, `422`
