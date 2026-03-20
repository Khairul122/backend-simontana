# FETCH Role: OperatorDesa (Full Response)

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="token_operator"
```

## 1) Laporan Workflow

Endpoint:
- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

Contoh `POST /laporans/1/verifikasi`:

```bash
curl -X POST "$BASE_URL/laporans/1/verifikasi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diverifikasi","catatan_verifikasi":"Valid"}'
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Laporan berhasil diverifikasi",
  "data": {
    "id": 1,
    "status": "Diverifikasi",
    "id_verifikator": 2,
    "catatan_verifikasi": "Valid",
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

### 422 Response Body (invalid transition)

```json
{
  "success": false,
  "message": "Transisi status tidak valid: Selesai -> Diproses",
  "code": "INVALID_STATUS_TRANSITION",
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

Contoh `GET /monitoring`:

```bash
curl -X GET "$BASE_URL/monitoring?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (Paginated)

```json
{
  "success": true,
  "message": "Data monitoring berhasil diambil",
  "data": [
    {
      "id_monitoring": 99,
      "id_laporan": 1,
      "id_operator": 2,
      "waktu_monitoring": "2026-03-20 08:00:00",
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

### 201 Response Body (POST)

```json
{
  "success": true,
  "message": "Monitoring berhasil dibuat",
  "data": {
    "id_monitoring": 100,
    "id_laporan": 1,
    "id_operator": 2,
    "hasil_monitoring": "Kondisi membaik"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Response Body (validasi)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "errors": {
    "laporan_id": [
      "The selected laporan id is invalid."
    ]
  },
  "details": {
    "laporan_id": [
      "The selected laporan id is invalid."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 403 Response Body

```json
{
  "success": false,
  "message": "Tidak memiliki izin untuk membuat monitoring pada laporan ini",
  "code": "INSUFFICIENT_PERMISSIONS",
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

### 201 Response Body (POST)

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

## 5) Matriks Status Semua Endpoint OperatorDesa

- `GET /laporans`
  - `200` -> list nested + `meta.pagination`

- `GET /laporans/{id}`
  - `200` -> detail nested lengkap
  - `404` -> resource not found

- `PUT /laporans/{id}`
  - `200` -> laporan updated (nested)
  - `403` -> forbidden policy
  - `422` -> validation error

- `GET /laporans/statistics`
  - `200` -> data statistik lengkap

- `POST /laporans/{id}/verifikasi`
  - `200` -> lihat section `200 Response Body`
  - `422` -> lihat section `422 Response Body (invalid transition)`
  - `404` -> laporan tidak ditemukan

- `POST /laporans/{id}/proses`
  - `200` -> shape sama, `status` menjadi `Diproses`/`Selesai`
  - `422` -> invalid transition

- `GET /laporans/{id}/riwayat`
  - `200` -> list riwayat tindakan
  - `404` -> laporan tidak ditemukan

- `GET /monitoring`
  - `200` -> lihat section paginated monitoring

- `POST /monitoring`
  - `201` -> lihat section `201 Response Body (POST)`
  - `403` -> lihat section `403 Response Body`
  - `422` -> lihat section `422 Response Body (validasi)`

- `GET /monitoring/{id}`
  - `200` -> detail monitoring nested
  - `403` -> forbidden policy
  - `404` -> monitoring tidak ditemukan

- `PUT /monitoring/{id}`
  - `200` -> monitoring updated nested
  - `403` -> forbidden policy
  - `404` -> monitoring tidak ditemukan
  - `422` -> validation error

- `DELETE /monitoring/{id}`
  - `200` ->

```json
{
  "success": true,
  "message": "Monitoring berhasil dihapus",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

  - `403` -> forbidden policy
  - `404` -> monitoring tidak ditemukan

- `GET /tindak-lanjut`
  - `200` -> paginated nested

- `POST /tindak-lanjut`
  - `201` -> lihat section `201 Response Body (POST)`
  - `403` -> forbidden policy
  - `422` -> validation error

- `GET /tindak-lanjut/{id}`
  - `200`, `403`, `404`

- `PUT /tindak-lanjut/{id}`
  - `200`, `403`, `404`, `422`

- `DELETE /tindak-lanjut/{id}`
  - `200`, `403`, `404`

- `GET /riwayat-tindakan`
  - `200` -> paginated nested

- `POST /riwayat-tindakan`
  - `201` -> lihat section `201 Response Body (POST)`
  - `403`, `422`

- `GET /riwayat-tindakan/{id}`
  - `200`, `403`, `404`

- `PUT /riwayat-tindakan/{id}`
  - `200`, `403`, `404`, `422`

- `DELETE /riwayat-tindakan/{id}`
  - `200`, `403`, `404`

## 4) Riwayat Tindakan

Endpoint:
- `GET /riwayat-tindakan`
- `POST /riwayat-tindakan`
- `GET /riwayat-tindakan/{id}`
- `PUT /riwayat-tindakan/{id}`
- `DELETE /riwayat-tindakan/{id}`

### 201 Response Body (POST)

```json
{
  "success": true,
  "message": "Riwayat tindakan berhasil dibuat",
  "data": {
    "id": 21,
    "tindaklanjut_id": 7,
    "id_petugas": 2,
    "keterangan": "Evakuasi tahap awal",
    "waktu_tindakan": "2026-03-20 10:00:00"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```
