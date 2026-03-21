# FETCH API Guide (Per Role + Full Response Body)

Dokumen ini adalah indeks utama fetch API dengan contoh response body yang lengkap.

## Daftar Dokumen

- `FETCH_PUBLIC.md` -> endpoint publik (tanpa JWT)
- `FETCH_WARGA.md` -> endpoint role `Warga`
- `FETCH_OPERATOR_DESA.md` -> endpoint role `OperatorDesa`
- `FETCH_PETUGAS_BPBD.md` -> endpoint role `PetugasBPBD`
- `FETCH_ADMIN.md` -> endpoint role `Admin`

## Base URL dan Header Standar

```bash
BASE_URL="http://127.0.0.1:8000/api/v1"
TOKEN="isi_token_jwt"
```

Header umum:
- `Accept: application/json`
- `Content-Type: application/json` (untuk JSON)
- `Authorization: Bearer $TOKEN` (endpoint protected)

## Kontrak Response Global

### Success (generic)

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {},
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 20,
      "total": 45,
      "from": 1,
      "to": 20
    }
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

### Error (generic)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "code": "VALIDATION_ERROR",
  "details": {
    "field": [
      "The field is required."
    ]
  },
  "errors": {
    "field": [
      "The field is required."
    ]
  },
  "request_id": "req_01HZY2P0W7D3G4"
}
```

## Status Reference

- `200`: sukses GET/PUT/POST tertentu
- `201`: resource created
- `400`: bad request (parameter/query tidak valid)
- `401`: unauthorized/token invalid
- `403`: forbidden/policy denied
- `404`: resource tidak ditemukan
- `422`: validation error / invalid transition
- `429`: rate limited
- `500`: internal error
