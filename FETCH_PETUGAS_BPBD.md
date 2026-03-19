# FETCH Role: PetugasBPBD

Dokumen ini untuk role `PetugasBPBD`.

## Setup

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="token_petugas"
```

## 1) Sesi dan Profil

- GET `/auth/me`
- POST `/auth/refresh`
- POST `/auth/logout`
- GET `/users/profile`
- PUT `/users/profile`

## 2) Laporan dan Workflow

- GET `/laporans`
- GET `/laporans/{id}`
- PUT `/laporans/{id}`
- GET `/laporans/statistics`
- POST `/laporans/{id}/verifikasi`
- POST `/laporans/{id}/proses`
- GET `/laporans/{id}/riwayat`

Contoh proses laporan:

```bash
curl -X POST "$BASE_URL/laporans/1/proses" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"status":"Diproses"}'
```

## 3) Monitoring, Tindak Lanjut, Riwayat

- GET/POST/PUT/DELETE `/monitoring`
- GET/POST/PUT/DELETE `/tindak-lanjut`
- GET/POST/PUT/DELETE `/riwayat-tindakan`

## 4) BMKG Protected

- GET `/bmkg`
- GET `/bmkg/cache/status`
- POST `/bmkg/cache/clear`

## 5) Referensi

- GET `/kategori-bencana`
- GET `/kategori-bencana/{id}`
- endpoint public `wilayah`

## Catatan

- PetugasBPBD bukan Admin, jadi endpoint admin-only akan `403`.
