# SIMONTA BENCANA API Documentation

**Version:** 2.0.0
**Base URL:** `http://localhost:8000/api`
**Authentication:** JWT Bearer Token

---

## 📋 Table of Contents

- [Authentication](#authentication)
- [User Management](#user-management)
- [Wilayah Management](#wilayah-management)
- [Kategori Bencana](#kategori-bencana)
- [Laporan Management](#laporan-management)
- [Tindak Lanjut](#tindak-lanjut)
- [Riwayat Tindakan](#riwayat-tindakan)
- [Monitoring](#monitoring)
- [BMKG Integration](#bmkg-integration)
- [Error Codes](#error-codes)

---

## 🔐 Authentication

### Overview
Semua endpoint (kecuali registration dan login) memerlukan JWT Token dalam header request.

### Authentication Header
```http
Authorization: Bearer {token}
```

### Endpoints

#### 1. Register User
**POST** `/auth/register`

Mendaftarkan pengguna baru dengan access token khusus.

**Request Body:**
```json
{
  "nama": "string",
  "username": "string",
  "email": "string",
  "password": "string",
  "password_confirmation": "string",
  "role": "Admin|PetugasBPBD|OperatorDesa|Warga",
  "no_telepon": "string (optional)",
  "alamat": "string (optional)",
  "id_desa": "integer (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User berhasil terdaftar",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

---

#### 2. Login
**POST** `/auth/login`

Autentikasi user dan mendapatkan JWT token.

**Request Body:**
```json
{
  "email": "string",
  "password": "string"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

---

#### 3. Get Current User
**GET** `/auth/me`

Mendapatkan data user yang sedang login (requires authentication).

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "John Doe",
    "email": "john@example.com",
    "role": "Admin"
  }
}
```

---

#### 4. Refresh Token
**POST** `/auth/refresh`

Memperbarui JWT token yang akan segera expired.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "new_token_here"
  }
}
```

---

#### 5. Logout
**POST** `/auth/logout`

Menghapus token dari whitelist.

**Response (200):**
```json
{
  "success": true,
  "message": "Logout berhasil"
}
```

---

## 👥 User Management

### Get All Users (Admin Only)
**GET** `/users`

Mendapatkan daftar semua pengguna dengan pagination.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| per_page | integer | No | Items per halaman (default: 15) |
| search | string | No | Search by nama, username, atau email |
| role | string | No | Filter by role (Admin, PetugasBPBD, OperatorDesa, Warga) |

**Response (200):**
```json
{
  "success": true,
  "message": "Data pengguna berhasil diambil",
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 100
  }
}
```

---

### Get User Statistics (Admin Only)
**GET** `/users/statistics`

Mendapatkan statistik pengguna.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_users": 150,
    "by_role": [...],
    "recent_users": [...],
    "users_this_month": 25
  }
}
```

---

### Get User Profile
**GET** `/users/profile`

Mendapatkan profil user yang sedang login.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nama": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "Admin",
    "desa": {...}
  }
}
```

---

### Update User Profile
**PUT** `/users/profile`

Memperbarui profil user yang sedang login.

**Request Body:**
```json
{
  "nama": "string (optional)",
  "no_telepon": "string (optional)",
  "alamat": "string (optional)",
  "id_desa": "integer (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profil berhasil diupdate",
  "data": {...}
}
```

---

### Create User (Admin Only)
**POST** `/users`

Menambah pengguna baru (Admin only).

**Request Body:**
```json
{
  "nama": "string",
  "username": "string",
  "email": "string",
  "password": "string",
  "role": "Admin|PetugasBPBD|OperatorDesa|Warga",
  "no_telepon": "string (optional)",
  "alamat": "string (optional)",
  "id_desa": "integer (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Pengguna berhasil ditambahkan",
  "data": {...}
}
```

---

### Get Specific User (Admin Only)
**GET** `/users/{id}`

Mendapatkan data pengguna spesifik (Admin only).

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

**Response (200):**
```json
{
  "success": true,
  "message": "Data pengguna berhasil diambil",
  "data": {...}
}
```

---

### Update User (Admin Only)
**PUT** `/users/{id}`

Update data pengguna (Admin only).

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

**Request Body:**
```json
{
  "nama": "string (optional)",
  "username": "string (optional)",
  "email": "string (optional)",
  "role": "string (optional)",
  "no_telepon": "string (optional)",
  "alamat": "string (optional)",
  "id_desa": "integer (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Pengguna berhasil diupdate",
  "data": {...}
}
```

---

### Delete User (Admin Only)
**DELETE** `/users/{id}`

Menghapus pengguna (Admin only).

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | User ID |

**Response (200):**
```json
{
  "success": true,
  "message": "Pengguna berhasil dihapus"
}
```

---

## 🗺️ Wilayah Management

### Get All Provinces
**GET** `/wilayah/provinsi`

Mendapatkan semua data provinsi.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| include | string | No | Include relasi (children untuk kabupaten) |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama": "Jawa Timur",
      "kabupaten": [...]
    }
  ]
}
```

---

### Get Province by ID
**GET** `/wilayah/provinsi/{id}`

Mendapatkan data provinsi spesifik.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Province ID |

---

### Get Regencies by Province
**GET** `/wilayah/kabupaten/{provinsi_id}`

Mendapatkan semua kabupaten/kota dalam provinsi.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| provinsi_id | integer | Yes | Province ID |

---

### Get Districts by Regency
**GET** `/wilayah/kecamatan/{kabupaten_id}`

Mendapatkan semua kecamatan dalam kabupaten.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| kabupaten_id | integer | Yes | Regency ID |

---

### Get Villages by District
**GET** `/wilayah/desa/{kecamatan_id}`

Mendapatkan semua desa/kelurahan dalam kecamatan.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| kecamatan_id | integer | Yes | District ID |

---

### Get Full Location Detail
**GET** `/wilayah/detail/{desa_id}`

Mendapatkan hierarki lokasi lengkap dari desa.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| desa_id | integer | Yes | Village ID |

---

### Search Wilayah
**GET** `/wilayah/search?q={keyword}`

Mencari data wilayah berdasarkan nama.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Keyword pencarian |

---

## 🏷️ Kategori Bencana

### Get All Categories
**GET** `/kategori-bencana`

Mendapatkan semua kategori bencana.

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nama_kategori": "Kebakaran",
      "deskripsi": "Kategori untuk kejadian kebakaran"
    }
  ]
}
```

---

### Get Category by ID
**GET** `/kategori-bencana/{id}`

Mendapatkan kategori spesifik.

---

### Create Category (Admin Only)
**POST** `/kategori-bencana`

Membuat kategori bencana baru (Admin only).

**Request Body:**
```json
{
  "nama_kategori": "string",
  "deskripsi": "string"
}
```

---

### Update Category (Admin Only)
**PUT** `/kategori-bencana/{id}`

Update kategori bencana (Admin only).

---

### Delete Category (Admin Only)
**DELETE** `/kategori-bencana/{id}`

Menghapus kategori bencana (Admin only).

---

## 📝 Laporan Management

### Get All Reports
**GET** `/laporans`

Mendapatkan semua laporan bencana dengan filter dan pagination.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status (Draft, Menunggu Verifikasi, etc.) |
| kategori_id | integer | No | Filter by kategori bencana |
| user_id | integer | No | Filter by pelapor |
| id_desa | integer | No | Filter by desa ID |
| prioritas | boolean | No | Filter laporan prioritas only |
| search | string | No | Search in judul, deskripsi, alamat |
| lat/lng/radius | number | No | Filter by location radius |
| limit | integer | No | Items per halaman (default: 15) |
| order_by | string | No | Kolom untuk ordering |
| order_direction | string | No | Arah ordering (asc/desc) |

**Response (200):**
```json
{
  "success": true,
  "message": "Data laporan berhasil diambil",
  "data": [...],
  "pagination": {...}
}
```

---

### Create Report
**POST** `/laporans`

Membuat laporan bencana baru dengan upload bukti foto/video.

**Request Body (multipart/form-data):**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| judul_laporan | string | Yes | Judul laporan |
| deskripsi | string | Yes | Deskripsi detail kejadian |
| tingkat_keparahan | string | Yes | Rendah, Sedang, Tinggi, Sangat Tinggi |
| latitude | number | Yes | Koordinat latitude (-90 to 90) |
| longitude | number | Yes | Koordinat longitude (-180 to 180) |
| id_kategori_bencana | integer | Yes | ID kategori bencana |
| id_desa | integer | Yes | ID desa lokasi |
| alamat | string | No | Alamat lengkap kejadian |
| jumlah_korban | integer | No | Jumlah korban jiwa |
| jumlah_rumah_rusak | integer | No | Jumlah rumah rusak |
| is_prioritas | boolean | No | Status prioritas |
| foto_bukti_1 | file | No | Upload foto bukti 1 (JPG/PNG max 5MB) |
| foto_bukti_2 | file | No | Upload foto bukti 2 (JPG/PNG max 5MB) |
| foto_bukti_3 | file | No | Upload foto bukti 3 (JPG/PNG max 5MB) |
| video_bukti | file | No | Upload video bukti (MP4/AVI/MOV max 10MB) |

**Response (201):**
```json
{
  "success": true,
  "message": "Laporan berhasil dibuat",
  "data": {
    "id": 1,
    "judul_laporan": "Kebakaran Hutan",
    "status": "Draft",
    "foto_bukti_1": "1703234567_abc.jpg",
    "foto_bukti_1_url": "http://localhost:8000/storage/laporans/1703234567_abc.jpg",
    ...
  }
}
```

---

### Get Report Detail
**GET** `/laporans/{id}`

Mendapatkan detail laporan lengkap dengan semua relasi.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Report ID |

**Response (200):**
```json
{
  "success": true,
  "message": "Detail laporan berhasil diambil",
  "data": {
    "id": 1,
    "pelapor": {...},
    "kategori": {...},
    "desa": {...},
    "tindakLanjut": [...],
    "monitoring": [...]
  }
}
```

---

### Update Report
**PUT** `/laporans/{id}`

Update data laporan (hanya pelapor atau Admin/PetugasBPBD yang berhak).

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Report ID |

**Request Body (multipart/form-data):**
Semua field sama dengan create, bersifat optional.

---

### Delete Report
**DELETE** `/laporans/{id}`

Menghapus laporan beserta file bukti (hanya pelapor atau Admin yang berhak).

---

### Get Report Statistics
**GET** `/laporans/statistics`

Mendapatkan statistik laporan bencana.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| period | string | No | weekly, monthly, yearly |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_laporan": 150,
    "laporan_perlu_verifikasi": 25,
    "laporan_ditindak": 45,
    "laporan_selesai": 60,
    "laporan_ditolak": 5,
    "weekly_stats": {...},
    "categories_stats": {...},
    "monthly_trend": {...}
  }
}
```

---

### Verify Report
**POST** `/laporans/{id}/verifikasi`

Verifikasi laporan oleh Operator Desa atau Petugas BPBD.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Report ID |

**Request Body:**
```json
{
  "status": "Diverifikasi|Ditolak",
  "catatan_verifikasi": "string (optional)"
}
```

---

### Process Report
**POST** `/laporans/{id}/proses`

Update status penanganan laporan oleh Petugas BPBD.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Report ID |

**Request Body:**
```json
{
  "status": "Diproses|Tindak Lanjut|Selesai",
  "catatan_penanganan": "string (optional)"
}
```

---

### Get Report History
**GET** `/laporans/{id}/riwayat`

Mengambil riwayat seluruh tindakan pada laporan.

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Report ID |

**Response (200):**
```json
{
  "success": true,
  "message": "Riwayat laporan berhasil diambil",
  "data": [
    {
      "id": 1,
      "aksi": "Verifikasi",
      "deskripsi": "Laporan diverifikasi",
      "user": {...}
    }
  ]
}
```

---

## ⚡ Tindak Lanjut

### Get All Follow-up Actions
**GET** `/tindak-lanjut`

Mengambil semua data tindak lanjut dengan filter.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| laporan_id | integer | No | Filter by laporan ID |
| id_petugas | integer | No | Filter by petugas ID |
| status | string | No | Filter by status (Menuju Lokasi, Selesai) |
| per_page | integer | No | Items per halaman (default: 20) |

---

### Create Follow-up Action
**POST** `/tindak-lanjut`

Membuat data tindak lanjut baru.

**Request Body:**
```json
{
  "laporan_id": "integer (required)",
  "id_petugas": "integer (required)",
  "tanggal_tanggapan": "datetime (required)",
  "status": "Menuju Lokasi|Selesai (optional)"
}
```

---

### Get Specific Follow-up
**GET** `/tindak-lanjut/{id}`

Mengambil data tindak lanjut berdasarkan ID.

---

### Update Follow-up
**PUT** `/tindak-lanjut/{id}`

Update data tindak lanjut.

**Request Body:**
```json
{
  "tanggal_tanggapan": "datetime (optional)",
  "status": "Menuju Lokasi|Selesai (optional)"
}
```

---

### Delete Follow-up
**DELETE** `/tindak-lanjut/{id}`

Menghapus data tindak lanjut.

---

## 📜 Riwayat Tindakan

### Get All Action History
**GET** `/riwayat-tindakan`

Mengambil semua riwayat tindakan.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tindaklanjut_id | integer | No | Filter by tindak lanjut ID |
| id_petugas | integer | No | Filter by petugas ID |
| per_page | integer | No | Items per halaman (default: 20) |

---

### Create Action History
**POST** `/riwayat-tindakan`

Membuat riwayat tindakan baru.

**Request Body:**
```json
{
  "tindaklanjut_id": "integer (required)",
  "id_petugas": "integer (required)",
  "keterangan": "string (required)",
  "waktu_tindakan": "datetime (required)"
}
```

---

## 📊 Monitoring

### Get All Monitoring
**GET** `/monitoring`

Mengambil semua data monitoring.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id_laporan | integer | No | Filter by laporan ID |
| id_operator | integer | No | Filter by operator ID |

---

### Create Monitoring
**POST** `/monitoring`

Membuat monitoring baru.

**Request Body:**
```json
{
  "id_laporan": "integer (required)",
  "id_operator": "integer (required)",
  "waktu_monitoring": "datetime (required)",
  "hasil_monitoring": "string (required)",
  "koordinat_gps": "string (optional)"
}
```

---

### Update Monitoring
**PUT** `/monitoring/{id}`

Update data monitoring.

---

### Delete Monitoring
**DELETE** `/monitoring/{id}`

Hapus data monitoring.

---

## 🌩️ BMKG Integration

### Get Latest Earthquake
**GET** `/bmkg/gempa/terbaru`

Data gempa terbaru (60 menit terakhir). **Public endpoint - no auth required**.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "tanggal": "2025-12-23T10:30:00Z",
    "magnitude": 5.2,
    "kedalaman": 10,
    "lokasi": "..."
  }
}
```

---

### Get Recent Earthquakes
**GET** `/bmkg/gempa/terkini`

Daftar gempa terkini. **Public endpoint - no auth required**.

---

### Get Felt Earthquakes
**GET** `/bmkg/gempa/dirasakan`

Gempa yang dirasakan. **Public endpoint - no auth required**.

---

### Get Tsunami Warning
**GET** `/bmkg/peringatan-tsunami`

Peringatan tsunami terkini. **Public endpoint - no auth required**.

---

### Get Weather Forecast
**GET** `/bmkg/prakiraan-cuaca`

Prakiraan cuaca per wilayah (requires authentication).

---

### Get All BMKG Data
**GET** `/bmkg`

Get semua data BMKG (requires authentication).

---

### Clear BMKG Cache
**POST** `/bmkg/cache/clear`

Hapus cache data BMKG (requires authentication).

---

## ❌ Error Codes

| Status Code | Meaning | Description |
|-------------|---------|-------------|
| 200 | OK | Request berhasil |
| 201 | Created | Resource berhasil dibuat |
| 400 | Bad Request | Request tidak valid |
| 401 | Unauthorized | Token tidak valid atau expired |
| 403 | Forbidden | Tidak memiliki akses |
| 404 | Not Found | Resource tidak ditemukan |
| 422 | Validation Error | Data input tidak valid |
| 500 | Internal Server Error | Terjadi kesalahan di server |

### Error Response Format
```json
{
  "success": false,
  "message": "Error description here",
  "errors": {...}
}
```

---

## 📌 Notes

### File Upload Format
- **Foto**: JPEG, PNG (max 5MB per file)
- **Video**: MP4, AVI, MOV (max 10MB)
- **Storage**: Files disimpan di `storage/app/public/laporans/`

### Pagination
Default items per page: 15-20 (tergantung endpoint)

### Role-based Access Control
- **Admin**: Full access
- **PetugasBPBD**: Can verify, process reports
- **OperatorDesa**: Can verify reports
- **Warga**: Can create/view own reports

---

## 🔗 Related Resources

- [Swagger UI](http://localhost:8000/api/documentation) - Interactive API documentation
- [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) - Laravel Swagger wrapper

---

**Generated:** 2025-12-23
**Version:** 2.0.0
**Contact:** dev@simonta.id
