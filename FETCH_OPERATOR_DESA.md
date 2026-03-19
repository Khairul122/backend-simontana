# FETCH Role: OperatorDesa

Dokumen ini untuk role `OperatorDesa`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="token_operator"
```

## 1) Sesi dan Profil

- GET `/auth/me`
- POST `/auth/refresh`
- POST `/auth/logout`
- GET `/users/profile`
- PUT `/users/profile`

## 2) Laporan Operasional

- GET `/laporans`
- GET `/laporans/{id}`
- PUT `/laporans/{id}`
- GET `/laporans/statistics`
- POST `/laporans/{id}/verifikasi`
- POST `/laporans/{id}/proses`
- GET `/laporans/{id}/riwayat`

Contoh verifikasi:

```bash
curl -X POST "$BASE_URL/laporans/1/verifikasi" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diverifikasi","catatan_verifikasi":"Valid"}'
```

## 3) Monitoring

- GET `/monitoring`
- POST `/monitoring`
- GET `/monitoring/{id}`
- PUT `/monitoring/{id}`
- DELETE `/monitoring/{id}`

Contoh create monitoring:

```bash
curl -X POST "$BASE_URL/monitoring" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "id_laporan":1,
    "id_operator":2,
    "waktu_monitoring":"2026-03-19 08:00:00",
    "hasil_monitoring":"Kondisi terkendali"
  }'
```

## 4) Tindak Lanjut

- GET `/tindak-lanjut`
- POST `/tindak-lanjut`
- GET `/tindak-lanjut/{id}`
- PUT `/tindak-lanjut/{id}`
- DELETE `/tindak-lanjut/{id}`

## 5) Riwayat Tindakan

- GET `/riwayat-tindakan`
- POST `/riwayat-tindakan`
- GET `/riwayat-tindakan/{id}`
- PUT `/riwayat-tindakan/{id}`
- DELETE `/riwayat-tindakan/{id}`

## 6) Referensi

- GET `/kategori-bencana`
- GET `/kategori-bencana/{id}`
- seluruh endpoint public `wilayah` dan `bmkg`
