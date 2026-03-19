# FETCH Role: Warga

Dokumen ini untuk user dengan role `Warga`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="token_warga"
```

## 1) Profil dan Sesi

- GET `/auth/me`
- POST `/auth/refresh`
- POST `/auth/logout`
- GET `/users/profile`
- PUT `/users/profile`
- GET `/check-token`

Contoh:

```bash
curl -X GET "$BASE_URL/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 2) Laporan (Warga)

- GET `/laporans`
- POST `/laporans`
- GET `/laporans/{id}`
- PUT `/laporans/{id}` (jika pemilik)
- DELETE `/laporans/{id}` (jika pemilik)
- GET `/laporans/statistics`
- GET `/laporans/{id}/riwayat`

Contoh create laporan:

```bash
curl -X POST "$BASE_URL/laporans" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "judul_laporan":"Banjir RT 03",
    "deskripsi":"Air setinggi lutut",
    "tingkat_keparahan":"Tinggi",
    "latitude":-6.2,
    "longitude":106.8,
    "id_kategori_bencana":1,
    "id_desa":1
  }'
```

## 3) Referensi Data

- GET `/kategori-bencana`
- GET `/kategori-bencana/{id}`

## 4) Catatan Akses

- Warga tidak boleh mengelola data operasional (`monitoring`, `tindak-lanjut`, `riwayat-tindakan`) secara umum.
- Jika memaksa akses, respon biasanya `403` dengan code permission.
