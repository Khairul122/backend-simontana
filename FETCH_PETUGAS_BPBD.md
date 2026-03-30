# FETCH Role: PetugasBPBD (Full Response)

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_petugas"
```

## 1) Workflow Laporan

Endpoint:
- `POST /laporans/{id}/verifikasi`
- `POST /laporans/{id}/proses`
- `GET /laporans/{id}/riwayat`

Contoh `POST /laporans/1/proses`:

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diproses"}'
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Status laporan berhasil diperbarui",
  "data": {
    "id": 1,
    "status": "Diproses",
    "id_penanggung_jawab": 3,
    "penanggungJawab": {
      "id": 3,
      "nama": "Petugas BPBD"
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Response Body

```json
{
  "success": false,
  "message": "Transisi status tidak valid: Ditolak -> Diproses",
  "code": "INVALID_STATUS_TRANSITION",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) Operasional

Endpoint:
- `GET/POST/PUT/DELETE /monitoring`
- `GET/POST/PUT/DELETE /tindak-lanjut`
- `GET/POST/PUT/DELETE /riwayat-tindakan`

Contoh `GET /tindak-lanjut`:

```bash
curl -X GET "$BASE_URL/tindak-lanjut?per_page=20" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (Paginated)

```json
{
  "success": true,
  "message": "Data tindak lanjut berhasil diambil",
  "data": [
    {
      "id_tindaklanjut": 7,
      "laporan_id": 1,
      "id_petugas": 3,
      "tanggal_tanggapan": "2026-03-20 09:00:00",
      "status": "Menuju Lokasi",
      "petugas": {
        "id": 3,
        "nama": "Petugas BPBD"
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
      "total": 23,
      "from": 1,
      "to": 20
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 403 Response Body

```json
{
  "success": false,
  "message": "Akses ditolak",
  "code": "FORBIDDEN",
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
    "waktu_tindakan": [
      "The waktu tindakan field is required."
    ]
  },
  "details": {
    "waktu_tindakan": [
      "The waktu tindakan field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) BMKG Protected

Endpoint:
- `GET /bmkg`
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

### 200 Response Body (`GET /bmkg`)

```json
{
  "success": true,
  "message": "Data BMKG berhasil diambil",
  "data": {
    "gempa_terbaru": {
      "Magnitude": "5.2",
      "Wilayah": "Selatan Jawa"
    },
    "daftar_gempa": [],
    "gempa_dirasakan": [],
    "cache_status": {
      "available": true,
      "ttl": 300
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 4) Matriks Status Semua Endpoint PetugasBPBD

- `GET /auth/me`, `GET /users/profile`
  - `200`, `401`

- `POST /auth/refresh`, `POST /auth/logout`
  - `200`, `401`

- `GET /check-token`
  - `200`, `401`

- `GET /laporans`, `GET /laporans/pelapor/{pelaporId}`, `GET /laporans/{id}`, `GET /laporans/statistics`
  - `200`
  - `404` khusus endpoint detail

- `PUT /laporans/{id}`
  - `200`, `403`, `422`, `404`

- `POST /laporans/{id}/verifikasi`, `POST /laporans/{id}/proses`
  - `200`, `403`, `404`, `422`

- `GET /laporans/{id}/riwayat`
  - `200`, `404`

- `GET/POST/PUT/DELETE /monitoring`
  - `GET`: `200`
  - `POST`: `201`, `403`, `422`
  - `PUT`: `200`, `403`, `404`, `422`
  - `DELETE`: `200`, `403`, `404`

- `GET/POST/PUT/DELETE /tindak-lanjut`
  - `GET`: `200`
  - `POST`: `201`, `403`, `422`
  - `PUT`: `200`, `403`, `404`, `422`
  - `DELETE`: `200`, `403`, `404`

- `GET/POST/PUT/DELETE /riwayat-tindakan`
  - `GET`: `200`
  - `POST`: `201`, `403`, `422`
  - `PUT`: `200`, `403`, `404`, `422`
  - `DELETE`: `200`, `403`, `404`

- `GET /bmkg`, `GET /bmkg/cache/status`, `POST /bmkg/cache/clear`
  - `200`, `500`
