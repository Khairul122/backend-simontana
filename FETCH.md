# FETCH API Guide (Per Role)

Dokumen fetch API sekarang dipisah **per role** agar lebih mudah dipakai tim frontend, QA, dan integrasi pihak ketiga.

## Struktur Dokumen

- `FETCH_PUBLIC.md` -> endpoint publik (tanpa token)
- `FETCH_WARGA.md` -> alur untuk role `Warga`
- `FETCH_OPERATOR_DESA.md` -> alur untuk role `OperatorDesa`
- `FETCH_PETUGAS_BPBD.md` -> alur untuk role `PetugasBPBD`
- `FETCH_ADMIN.md` -> alur untuk role `Admin`

## Kontrak Respons (Berlaku Umum)

### Sukses

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "request_id": "req_xxx"
}
```

### Gagal

```json
{
  "success": false,
  "message": "...",
  "code": "ERROR_CODE",
  "details": {},
  "request_id": "req_xxx"
}
```

### Error umum

- `401`: `TOKEN_MISSING`, `TOKEN_INVALID`, `TOKEN_EXPIRED`, `UNAUTHORIZED`
- `403`: `INSUFFICIENT_PERMISSIONS` / `FORBIDDEN`
- `422`: validasi gagal, termasuk `INVALID_STATUS_TRANSITION` pada workflow laporan
- `429`: throttle login/register

## Base URL & Header Dasar

```bash
BASE_URL="http://127.0.0.1:8000/api"
TOKEN="isi_token_jwt"
```

Header umum:

- `Accept: application/json`
- `Content-Type: application/json` (untuk body JSON)
- `Authorization: Bearer <TOKEN>` (endpoint protected)

## Alur Cepat

1. Buka `FETCH_PUBLIC.md` untuk login/register.
2. Ambil JWT token dari login.
3. Lanjut ke dokumen sesuai role user kamu.
