# FETCH Referensi: BMKG (Full Response)

Endpoint ini bersifat Public dan bisa diakses oleh role apa saja (maupun publik tanpa token) untuk mendapatkan data cuaca dan gempa dari BMKG yang sudah di-cache oleh sistem Simonta.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
# Jika endpoint protected, gunakan token
# TOKEN="token_api"
```

---

## 1) GET `/bmkg` (Dashboard Summary)

Menampilkan ringkasan data BMKG (gempa terbaru, dirasakan, terkini, dan status cache).

```bash
curl -X GET "$BASE_URL/bmkg" \
  -H "Accept: application/json"
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Data BMKG berhasil diambil",
  "data": {
    "gempa_terbaru": {
      "Tanggal": "21 Mar 2026",
      "Jam": "10:15:30 WIB",
      "DateTime": "2026-03-21T03:15:30+00:00",
      "Coordinates": "-8.15,108.82",
      "Lintang": "8.15 LS",
      "Bujur": "108.82 BT",
      "Magnitude": "5.4",
      "Kedalaman": "12 km",
      "Wilayah": "105 km BaratDaya PANGANDARAN-JABAR",
      "Potensi": "Tidak berpotensi tsunami",
      "Dirasakan": "III Pangandaran, II Cilacap",
      "Shakemap": "20260321101530.mmi.jpg"
    },
    "daftar_gempa": [
      {
        "Tanggal": "21 Mar 2026",
        "Jam": "08:12:11 WIB",
        "DateTime": "2026-03-21T01:12:11+00:00",
        "Coordinates": "-2.15,140.21",
        "Lintang": "2.15 LS",
        "Bujur": "140.21 BT",
        "Magnitude": "5.1",
        "Kedalaman": "10 km",
        "Wilayah": "32 km TimurLaut JAYAPURA-PAPUA"
      }
    ],
    "gempa_dirasakan": [
      {
        "Tanggal": "21 Mar 2026",
        "Jam": "10:15:30 WIB",
        "Coordinates": "-8.15,108.82",
        "Magnitude": "5.4",
        "Wilayah": "105 km BaratDaya PANGANDARAN-JABAR",
        "Dirasakan": "III Pangandaran, II Cilacap"
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

---

## 2) GET `/bmkg/gempa/terbaru`

Menampilkan informasi 1 gempa bumi paling baru yang dicatat BMKG. Termasuk info shakemap (peta guncangan).

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terbaru" \
  -H "Accept: application/json"
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Data gempa terbaru berhasil diambil",
  "data": {
    "Tanggal": "21 Mar 2026",
    "Jam": "10:15:30 WIB",
    "DateTime": "2026-03-21T03:15:30+00:00",
    "Coordinates": "-8.15,108.82",
    "Lintang": "8.15 LS",
    "Bujur": "108.82 BT",
    "Magnitude": "5.4",
    "Kedalaman": "12 km",
    "Wilayah": "105 km BaratDaya PANGANDARAN-JABAR",
    "Potensi": "Tidak berpotensi tsunami",
    "Dirasakan": "III Pangandaran, II Cilacap",
    "Shakemap": "20260321101530.mmi.jpg"
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 3) GET `/bmkg/gempa/terkini`

Menampilkan daftar 15 gempa bumi M >= 5.0 terkini.

```bash
curl -X GET "$BASE_URL/bmkg/gempa/terkini" \
  -H "Accept: application/json"
```

### 200 Response Body (Array)

```json
{
  "success": true,
  "message": "Daftar gempa berhasil diambil",
  "data": [
    {
      "Tanggal": "21 Mar 2026",
      "Jam": "10:15:30 WIB",
      "DateTime": "2026-03-21T03:15:30+00:00",
      "Coordinates": "-8.15,108.82",
      "Lintang": "8.15 LS",
      "Bujur": "108.82 BT",
      "Magnitude": "5.4",
      "Kedalaman": "12 km",
      "Wilayah": "105 km BaratDaya PANGANDARAN-JABAR",
      "Potensi": "Tidak berpotensi tsunami"
    },
    {
      "Tanggal": "20 Mar 2026",
      "Jam": "18:22:10 WIB",
      "DateTime": "2026-03-20T11:22:10+00:00",
      "Coordinates": "-9.12,112.55",
      "Lintang": "9.12 LS",
      "Bujur": "112.55 BT",
      "Magnitude": "5.0",
      "Kedalaman": "20 km",
      "Wilayah": "120 km Tenggara KAB-MALANG-JATIM",
      "Potensi": "Tidak berpotensi tsunami"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 4) GET `/bmkg/gempa/dirasakan`

Menampilkan daftar 15 gempa bumi yang dilaporkan dirasakan oleh masyarakat (skala MMI).

```bash
curl -X GET "$BASE_URL/bmkg/gempa/dirasakan" \
  -H "Accept: application/json"
```

### 200 Response Body (Array)

```json
{
  "success": true,
  "message": "Data gempa dirasakan berhasil diambil",
  "data": [
    {
      "Tanggal": "21 Mar 2026",
      "Jam": "10:15:30 WIB",
      "DateTime": "2026-03-21T03:15:30+00:00",
      "Coordinates": "-8.15,108.82",
      "Lintang": "8.15 LS",
      "Bujur": "108.82 BT",
      "Magnitude": "5.4",
      "Kedalaman": "12 km",
      "Wilayah": "105 km BaratDaya PANGANDARAN-JABAR",
      "Dirasakan": "III Pangandaran, II Cilacap"
    }
  ],
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 5) GET `/bmkg/prakiraan-cuaca?wilayah_id=XXX`

Menampilkan prakiraan cuaca komplit untuk suatu wilayah berdasarkan ID Kabupaten dari database lokal yang dipetakan ke kode wilayah BMKG.

**Parameter:**
- `wilayah_id` (integer) - ID kabupaten/kota dari tabel `kabupatens`.

```bash
# Contoh 3171 adalah ID untuk Jakarta Pusat (tergantung master data DB)
curl -X GET "$BASE_URL/bmkg/prakiraan-cuaca?wilayah_id=3171" \
  -H "Accept: application/json"
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Data prakiraan cuaca berhasil diambil",
  "data": {
    "wilayah": "Jakarta Pusat",
    "provinsi": "DKI Jakarta",
    "timezone": "WIB",
    "cuaca": [
      {
        "jam": "2026-03-21 12:00:00",
        "cuaca": "Cerah Berawan",
        "suhu": "32",
        "kelembapan": "65",
        "angin_kecepatan": "10",
        "angin_arah": "Barat Laut",
        "icon": "http://example-cdn.com/icon/cerah-berawan.png"
      },
      {
        "jam": "2026-03-21 18:00:00",
        "cuaca": "Hujan Ringan",
        "suhu": "28",
        "kelembapan": "80",
        "angin_kecepatan": "10",
        "angin_arah": "Barat Daya",
        "icon": "http://example-cdn.com/icon/hujan.png"
      }
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### 422 Response Body (Validasi)

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
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 6) GET `/bmkg/peringatan-tsunami`

Mengambil peringatan tsunami terbaru jika ada.

```bash
curl -X GET "$BASE_URL/bmkg/peringatan-tsunami" \
  -H "Accept: application/json"
```

### 200 Response Body

```json
{
  "success": true,
  "message": "Data peringatan tsunami berhasil diambil",
  "data": {
    "status": "Tidak Ada Peringatan",
    "keterangan": "Saat ini tidak tercatat adanya peringatan dini tsunami dari BMKG."
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 7) Cache Management (Admin / BPBD)

Endpoint ini membutuhkan Token Authorization dan hanya role tertentu yang bisa memanggilnya (jika diprotect).

```bash
# Cek Status Cache
curl -X GET "$BASE_URL/bmkg/cache/status" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# Bersihkan Cache (Force Refresh Data)
curl -X POST "$BASE_URL/bmkg/cache/clear" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 200 Response Body (`/bmkg/cache/status`)

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

### 200 Response Body (`/bmkg/cache/clear`)

```json
{
  "success": true,
  "message": "Cache BMKG berhasil dibersihkan",
  "data": null,
  "request_id": "req_01HZY2P0W7D3G4"
}
```

---

## 8) Matriks Error Global

Semua call ke endpoint `/bmkg/*` dapat menghasilkan error dari server upstream BMKG jika website BMKG sedang down atau datanya korup (XML tidak valid):

### 500 Response Body

```json
{
  "success": false,
  "message": "Gagal menghubungi server BMKG: Timeout",
  "code": "EXTERNAL_API_ERROR",
  "request_id": "req_01HZY2P0W7D3G4"
}
```
