# FETCH BMKG (Lengkap)

Dokumen ini fokus untuk domain BMKG: endpoint public dan endpoint protected (cache management).

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="token_admin_atau_petugas"
```

## Endpoint Ringkas

Public:

- `GET /bmkg/gempa/terbaru`
- `GET /bmkg/gempa/terkini`
- `GET /bmkg/gempa/dirasakan`
- `GET /bmkg/prakiraan-cuaca?wilayah_id=...`
- `GET /bmkg/peringatan-dini-cuaca`

Protected:

- `GET /bmkg` (ringkasan)
- `GET /bmkg/cache/status`
- `POST /bmkg/cache/clear`

## 1) GET `/bmkg` (Protected Ringkasan)

### CURL

```bash
curl -X GET "$BASE_URL/bmkg" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data BMKG berhasil diambil",
  "data": {
    "gempa_terbaru": {
      "Tanggal": "31 Mar 2026",
      "Jam": "08:10:01 WIB",
      "Magnitude": "5.2",
      "Kedalaman": "10 km",
      "Wilayah": "Selatan Jawa",
      "Potensi": "Tidak berpotensi tsunami"
    },
    "daftar_gempa": [
      {
        "Tanggal": "31 Mar 2026",
        "Jam": "07:20:11 WIB",
        "Magnitude": "5.1",
        "Wilayah": "Timur Laut Jayapura"
      }
    ],
    "gempa_dirasakan": [
      {
        "Tanggal": "31 Mar 2026",
        "Jam": "06:35:45 WIB",
        "Magnitude": "4.9",
        "Wilayah": "Barat Daya Pangandaran",
        "Dirasakan": "III Pangandaran"
      }
    ],
    "cache_status": {
      "available": true,
      "ttl": 300
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 2) GET `/bmkg/gempa/terbaru`

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terbaru" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data gempa terbaru berhasil diambil",
  "data": {
    "Tanggal": "31 Mar 2026",
    "Jam": "08:10:01 WIB",
    "DateTime": "2026-03-31T01:10:01+00:00",
    "Coordinates": "-8.15,108.82",
    "Lintang": "8.15 LS",
    "Bujur": "108.82 BT",
    "Magnitude": "5.2",
    "Kedalaman": "10 km",
    "Wilayah": "105 km BaratDaya Pangandaran",
    "Potensi": "Tidak berpotensi tsunami",
    "Dirasakan": "III Pangandaran, II Cilacap",
    "Shakemap": "20260331081001.mmi.jpg"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404 Full Response Body

```json
{
  "success": false,
  "message": "Data gempa terbaru tidak tersedia",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 3) GET `/bmkg/gempa/terkini`

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terkini" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Daftar gempa berhasil diambil",
  "data": [
    {
      "Tanggal": "31 Mar 2026",
      "Jam": "08:10:01 WIB",
      "DateTime": "2026-03-31T01:10:01+00:00",
      "Coordinates": "-8.15,108.82",
      "Magnitude": "5.2",
      "Kedalaman": "10 km",
      "Wilayah": "Selatan Jawa"
    },
    {
      "Tanggal": "31 Mar 2026",
      "Jam": "07:10:22 WIB",
      "DateTime": "2026-03-31T00:10:22+00:00",
      "Coordinates": "-2.15,140.21",
      "Magnitude": "5.1",
      "Kedalaman": "11 km",
      "Wilayah": "Jayapura"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 4) GET `/bmkg/gempa/dirasakan`

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/gempa/dirasakan" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data gempa dirasakan berhasil diambil",
  "data": [
    {
      "Tanggal": "31 Mar 2026",
      "Jam": "08:10:01 WIB",
      "Magnitude": "5.2",
      "Wilayah": "Selatan Jawa",
      "Dirasakan": "III Pangandaran, II Cilacap"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 5) GET `/bmkg/prakiraan-cuaca?wilayah_id=...`

`wilayah_id` wajib diisi, contoh: `31.71.03.1001`.

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/prakiraan-cuaca?wilayah_id=31.71.03.1001" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data prakiraan cuaca berhasil diambil",
  "data": {
    "lokasi": {
      "provinsi": "DKI Jakarta",
      "kotkab": "Kota Jakarta Pusat",
      "kecamatan": "Sawah Besar",
      "desa": "Gunung Sahari Utara",
      "lat": -6.1555,
      "lon": 106.8344,
      "timezone": "Asia/Jakarta"
    },
    "cuaca": [
      [
        {
          "datetime": "2026-03-31T06:00:00+00:00",
          "local_datetime": "2026-03-31 13:00:00",
          "t": 32,
          "hu": 65,
          "weather_desc": "Cerah Berawan",
          "weather_desc_en": "Partly Cloudy",
          "ws": 10,
          "wd": "Barat Laut",
          "tcc": 25,
          "vs_text": "> 10 km"
        }
      ]
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Full Response Body

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
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 6) GET `/bmkg/peringatan-dini-cuaca`

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/peringatan-dini-cuaca" \
  -H "Accept: application/json"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Data peringatan dini cuaca berhasil diambil",
  "data": {
    "alerts": [
      {
        "title": "Peringatan Dini Cuaca DKI Jakarta",
        "link": "https://www.bmkg.go.id/cuaca/peringatan-dini.bmkg?prov=DKI",
        "description": "Berpotensi terjadi hujan sedang-lebat...",
        "author": "BMKG",
        "pubDate": "Tue, 31 Mar 2026 14:10:00 +0700",
        "lastBuildDate": "Tue, 31 Mar 2026 07:10:00 UTC"
      }
    ],
    "updated_at": "2026-03-31T07:15:30Z"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 7) GET `/bmkg/cache/status` (Protected)

### CURL

```bash
curl -X GET "$BASE_URL/bmkg/cache/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Status cache berhasil diambil",
  "data": {
    "driver": "file",
    "cached_keys": {
      "bmkg_gempa_terbaru": true,
      "bmkg_gempa_terkini": false,
      "bmkg_gempa_dirasakan": true
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 8) POST `/bmkg/cache/clear` (Protected)

### CURL

```bash
curl -X POST "$BASE_URL/bmkg/cache/clear" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Full Response Body

```json
{
  "success": true,
  "message": "Cache BMKG berhasil dibersihkan",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## 9) Error Mapping BMKG

### 401 (endpoint protected)

```json
{
  "success": false,
  "message": "Tidak terautentikasi",
  "code": "UNAUTHORIZED",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 404 (data upstream tidak ada)

```json
{
  "success": false,
  "message": "Data prakiraan cuaca tidak tersedia",
  "code": "RESOURCE_NOT_FOUND",
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 500 (upstream/downstream error)

```json
{
  "success": false,
  "message": "Gagal mengambil data BMKG",
  "code": "INTERNAL_SERVER_ERROR",
  "request_id": "req_01HZY2P0W7D3G4"
}
```
