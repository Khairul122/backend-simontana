# FETCH BMKG

Panduan endpoint BMKG (public + protected) dengan contoh CURL, request, dan response.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_admin_atau_petugas"
```

## Ringkasan Endpoint

Public:

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=...`
- `GET /bmkg/peringatan-dini-cuaca`

Protected:

- `GET /bmkg`
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

## 1) GET /bmkg (Protected Aggregated)

Mengembalikan ringkasan data BMKG sekaligus status cache.

### CURL

```bash
curl -X GET "$BASE_URL/bmkg" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response

```json
{
  "success": true,
  "message": "Data BMKG berhasil diambil",
  "data": {
    "gempa_terbaru": {},
    "daftar_gempa": {},
    "gempa_dirasakan": {},
    "cache_status": {
      "cache_duration_minutes": 60,
      "gempa_terbaru_cached": true,
      "daftar_gempa_cached": true,
      "gempa_dirasakan_cached": false,
      "peringatan_dini_cuaca_cached": true,
      "prakiraan_cuaca_keys_count": 12
    }
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 2) GET /bmkg/gempa/terbaru

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terbaru" \
  -H "Accept: application/json"
```

### 200 Response

```json
{
  "success": true,
  "message": "Data gempa terbaru berhasil diambil",
  "data": {
    "Infogempa": {
      "gempa": {
        "Tanggal": "01 Apr 2026",
        "Jam": "10:01:22 WIB",
        "Magnitude": "5.1",
        "Kedalaman": "10 km",
        "Wilayah": "Barat Daya Jawa",
        "Potensi": "Tidak berpotensi tsunami",
        "Shakemap": "https://static.bmkg.go.id/path/to/shakemap.jpg"
      }
    }
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 3) GET /bmkg/gempa/terkini

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terkini" \
  -H "Accept: application/json"
```

## 4) GET /bmkg/gempa/dirasakan

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/dirasakan" \
  -H "Accept: application/json"
```

## 5) GET /bmkg/prakiraan-cuaca

Field query wajib:

- `wilayah_id` (string kode adm4)

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/prakiraan-cuaca?wilayah_id=31.71.03.1001" \
  -H "Accept: application/json"
```

### 422 Response (wilayah_id kosong)

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
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 6) GET /bmkg/peringatan-dini-cuaca

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/peringatan-dini-cuaca" \
  -H "Accept: application/json"
```

## 7) GET /bmkg/cache/status (Protected)

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/cache/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response

```json
{
  "success": true,
  "message": "Status cache berhasil diambil",
  "data": {
    "cache_duration_minutes": 60,
    "gempa_terbaru_cached": true,
    "daftar_gempa_cached": true,
    "gempa_dirasakan_cached": true,
    "peringatan_dini_cuaca_cached": true,
    "prakiraan_cuaca_keys_count": 3
  },
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## 8) POST /bmkg/cache/clear (Protected)

### CURL

```bash
curl -X POST "$BASE_URL/bmkg/cache/clear" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response

```json
{
  "success": true,
  "message": "Cache BMKG berhasil dibersihkan",
  "request_id": "f79802f8-4778-4a8f-a6cc-2794f20a2ee2"
}
```

## Error Mapping BMKG

- `401` untuk endpoint protected tanpa token valid
- `404` jika data upstream tidak tersedia
- `422` jika query/param tidak valid
- `500` jika terjadi gangguan upstream/downstream atau internal
