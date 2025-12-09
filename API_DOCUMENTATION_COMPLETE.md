# SIMONTA BENCANA API Documentation (Complete)

## Access Information
- **Base URL**: `http://127.0.0.1:8001/api`
- **Authentication**: Bearer Token (JWT)
- **Default Login**: username "admintest", password "123456"
- **Swagger UI**: `http://127.0.0.1:8001/api/documentation`

## AUTHENTICATION ENDPOINTS

### 1. Refresh JWT Token
**POST** `/auth/refresh`
- **Description**: Memperbarui JWT token yang masih valid
- **Authentication**: Bearer Token (JWT)
- **Headers**: `Authorization: Bearer {token}`
- **Response**:
```json
{
    "success": true,
    "message": "Token berhasil diperbarui",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

## ADMIN MANAGEMENT ENDPOINTS

### 1. Get All Users
**GET** `/admin/pengguna`
- **Description**: Mendapatkan semua data pengguna (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Response**:
```json
{
    "success": true,
    "message": "Data pengguna berhasil diambil",
    "data": [
        {
            "id": 1,
            "nama": "Administrator",
            "username": "admin",
            "email": "admin@example.com",
            "role": "Admin",
            "no_telepon": "08123456789",
            "alamat": "Jl. Contoh No. 123"
        }
    ]
}
```

### 2. Create User
**POST** `/admin/pengguna`
- **Description**: Menambahkan pengguna baru (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama": "John Doe",
    "username": "johndoe",
    "password": "123456",
    "role": "Warga",
    "email": "john@example.com",
    "no_telepon": "08123456789",
    "alamat": "Jl. Contoh No. 123"
}
```
- **Response**:
```json
{
    "success": true,
    "message": "Pengguna berhasil ditambahkan",
    "data": {
        "id": 2,
        "nama": "John Doe",
        "username": "johndoe",
        "role": "Warga"
    }
}
```

### 3. Update User
**PUT** `/admin/pengguna/{id}`
- **Description**: Memperbarui data pengguna (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama": "John Doe Updated",
    "email": "john.updated@example.com",
    "no_telepon": "08123456789",
    "alamat": "Jl. Contoh No. 456"
}
```

### 4. Delete User
**DELETE** `/admin/pengguna/{id}`
- **Description**: Menghapus pengguna (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Response**:
```json
{
    "success": true,
    "message": "Pengguna berhasil dihapus"
}
```

### 5. Get All Kategori Bencana
**GET** `/admin/kategori-bencana`
- **Description**: Mendapatkan semua kategori bencana (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Response**:
```json
{
    "success": true,
    "message": "Daftar kategori bencana berhasil diambil",
    "data": [
        {
            "id_kategori": 1,
            "nama_kategori": "Banjir",
            "created_at": "2024-12-10T10:30:00.000000Z",
            "updated_at": "2024-12-10T10:30:00.000000Z"
        }
    ]
}
```

### 6. Create Kategori Bencana
**POST** `/admin/kategori-bencana`
- **Description**: Menambahkan kategori bencana baru (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama_kategori": "Kebakaran Hutan"
}
```

### 7. Get Kategori Bencana by ID
**GET** `/admin/kategori-bencana/{id}`
- **Description**: Mendapatkan detail kategori bencana (Admin only)
- **Authentication**: Bearer Token (Admin role required)

### 8. Update Kategori Bencana
**PUT** `/admin/kategori-bencana/{id}`
- **Description**: Memperbarui kategori bencana (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama_kategori": "Banjir Bandang"
}
```

### 9. Delete Kategori Bencana
**DELETE** `/admin/kategori-bencana/{id}`
- **Description**: Menghapus kategori bencana (Admin only)
- **Authentication**: Bearer Token (Admin role required)

### 10. Get Kategori Bencana Statistics
**GET** `/admin/kategori-bencana-statistics`
- **Description**: Mendapatkan statistik kategori bencana (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Response**:
```json
{
    "success": true,
    "message": "Statistik kategori bencana berhasil diambil",
    "data": {
        "total_kategori": 8,
        "kategori_terbanyak_laporan": {
            "id_kategori": 1,
            "nama_kategori": "Banjir",
            "laporan_count": 25
        }
    }
}
```

### 11. Get All Desa
**GET** `/admin/desa`
- **Description**: Mendapatkan semua data desa (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Query Parameters**:
  - `search`: Search desa by name, kecamatan, or kabupaten
  - `kecamatan`: Filter by kecamatan
  - `kabupaten`: Filter by kabupaten
  - `per_page`: Items per page for pagination (default: 15)

### 12. Create Desa
**POST** `/admin/desa`
- **Description**: Menambahkan desa baru (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama_desa": "Desa Baru",
    "kecamatan": "Kecamatan Baru",
    "kabupaten": "Kabupaten Baru"
}
```

### 13. Get Desa by ID
**GET** `/admin/desa/{id}`
- **Description**: Mendapatkan detail desa (Admin only)
- **Authentication**: Bearer Token (Admin role required)

### 14. Update Desa
**PUT** `/admin/desa/{id}`
- **Description**: Memperbarui data desa (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Request Body**:
```json
{
    "nama_desa": "Desa Updated",
    "kecamatan": "Kecamatan Updated",
    "kabupaten": "Kabupaten Updated"
}
```

### 15. Delete Desa
**DELETE** `/admin/desa/{id}`
- **Description**: Menghapus desa (Admin only)
- **Authentication**: Bearer Token (Admin role required)

### 16. Get Desa Statistics
**GET** `/admin/desa-statistics`
- **Description**: Mendapatkan statistik desa (Admin only)
- **Authentication**: Bearer Token (Admin role required)
- **Response**:
```json
{
    "success": true,
    "message": "Statistik desa berhasil diambil",
    "data": {
        "total_desa": 150,
        "total_kecamatan": 25,
        "total_kabupaten": 8
    }
}
```

## BPBD MANAGEMENT ENDPOINTS

### 1. Get BPBD Reports
**GET** `/bpbd/reports`
- **Description**: Mendapatkan laporan untuk BPBD
- **Authentication**: Bearer Token (PetugasBPBD role required)
- **Query Parameters**:
  - `status`: Filter by status (Baru, Diproses, Selesai)
  - `kategori`: Filter by kategori bencana
  - `date_from`: Filter tanggal dari
  - `date_to`: Filter tanggal sampai
  - `per_page`: Items per page

### 2. Get BPBD Report Details
**GET** `/bpbd/reports/{id}`
- **Description**: Mendapatkan detail laporan untuk BPBD
- **Authentication**: Bearer Token (PetugasBPBD role required)

### 3. Create Response
**POST** `/bpbd/reports/{id}/response`
- **Description**: Membuat respons/tindakan untuk laporan
- **Authentication**: Bearer Token (PetugasBPBD role required)
- **Request Body**:
```json
{
    "jenis_tindakan": "Evakuasi",
    "deskripsi": "Melakukan evakuasi warga ke lokasi aman",
    "prioritas": "Tinggi",
    "estimasi_waktu": "2 jam",
    "personil": "5 orang",
    "sumber_daya": "Mobil evakuasi, peralatan medis"
}
```

### 4. Update Response
**PUT** `/bpbd/responses/{id}`
- **Description**: Memperbarui respons/tindakan
- **Authentication**: Bearer Token (PetugasBPBD role required)
- **Request Body**:
```json
{
    "status_tindakan": "Sedang Berlangsung",
    "progress": "50%",
    "catatan": "Evakuasi berjalan dengan baik"
}
```

### 5. Get BPBD Statistics
**GET** `/bpbd/statistics`
- **Description**: Mendapatkan statistik laporan BPBD
- **Authentication**: Bearer Token (PetugasBPBD role required)
- **Response**:
```json
{
    "success": true,
    "message": "Statistik BPBD berhasil diambil",
    "data": {
        "total_laporan": 150,
        "laporan_baru": 25,
        "laporan_diproses": 75,
        "laporan_selesai": 50
    }
}
```

## OPERATOR MANAGEMENT ENDPOINTS

### 1. Get Operator Reports
**GET** `/operator/reports`
- **Description**: Mendapatkan laporan di wilayah desa operator
- **Authentication**: Bearer Token (OperatorDesa role required)
- **Response**:
```json
{
    "success": true,
    "message": "Operator village reports",
    "data": [
        {
            "id_laporan": 1,
            "id_kategori": 1,
            "lokasi": "Contoh Lokasi",
            "deskripsi": "Deskripsi laporan",
            "status_laporan": "Baru",
            "tanggal_lapor": "2024-12-10T10:30:00.000000Z",
            "kategori": {
                "id_kategori": 1,
                "nama_kategori": "Banjir"
            }
        }
    ]
}
```

### 2. Verify Report
**POST** `/operator/reports/{id}/verify`
- **Description**: Verifikasi laporan dari warga
- **Authentication**: Bearer Token (OperatorDesa role required)
- **Request Body**:
```json
{
    "status": "Valid",
    "catatan": "Laporan telah diverifikasi dan benar"
}
```

### 3. Create Monitoring
**POST** `/operator/reports/{id}/monitor`
- **Description**: Membuat catatan monitoring
- **Authentication**: Bearer Token (OperatorDesa role required)
- **Request Body**:
```json
{
    "status_monitoring": "Dalam Pantauan",
    "lokasi_monitoring": "Koordinat lokasi monitoring",
    "catatan_monitoring": "Kondisi terkendali",
    "foto_bukti": "path/to/foto.jpg"
}
```

### 4. Get Evacuation Sites
**GET** `/operator/evacuation-sites`
- **Description**: Mendapatkan data lokasi evakuasi
- **Authentication**: Bearer Token (OperatorDesa role required)
- **Response**:
```json
{
    "success": true,
    "message": "Operator evacuation sites",
    "data": [
        {
            "id": 1,
            "nama_lokasi": "Pos Evakuasi Utama",
            "alamat": "Jl. Evakuasi No. 123",
            "kapasitas": 500,
            "koordinat": "-6.200000,106.816666",
            "fasilitas": "Makanan, Medis, Listrik"
        }
    ]
}
```

### 5. Add Evacuation Site
**POST** `/operator/evacuation-sites`
- **Description**: Menambah lokasi evakuasi baru
- **Authentication**: Bearer Token (OperatorDesa role required)
- **Request Body**:
```json
{
    "nama_lokasi": "Pos Evakuasi Baru",
    "alamat": "Jl. Evakuasi No. 456",
    "kapasitas": 300,
    "koordinat": "-6.210000,106.820000",
    "fasilitas": "Makanan, Medis"
}
```

## DESA PUBLIC ENDPOINTS

### 1. Get All Kecamatan
**GET** `/desa-list/kecamatan`
- **Description**: Mendapatkan daftar semua kecamatan
- **Authentication**: Bearer Token (Any authenticated user)
- **Response**:
```json
{
    "success": true,
    "message": "Daftar kecamatan berhasil diambil",
    "data": [
        "Kecamatan A",
        "Kecamatan B",
        "Kecamatan C"
    ]
}
```

### 2. Get All Kabupaten
**GET** `/desa-list/kabupaten`
- **Description**: Mendapatkan daftar semua kabupaten
- **Authentication**: Bearer Token (Any authenticated user)
- **Response**:
```json
{
    "success": true,
    "message": "Daftar kabupaten berhasil diambil",
    "data": [
        "Kabupaten A",
        "Kabupaten B",
        "Kabupaten C"
    ]
}
```

## Error Responses

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthorized",
    "error": "Token tidak valid atau kadaluwarsa"
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "Forbidden",
    "error": "Akses ditolak - role tidak cukup"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Resource tidak ditemukan"
}
```

### 422 Validation Error
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "field_name": ["Error message untuk field"]
    }
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Terjadi kesalahan pada server",
    "error": "Internal server error message"
}
```

## Notes
- Semua endpoint yang memerlukan authentication harus menyertakan header `Authorization: Bearer {token}`
- Token JWT bisa didapatkan dengan melakukan login ke endpoint `/api/auth/login`
- Role-based access control diterapkan pada setiap endpoint
- Pagination menggunakan parameter `per_page` dan default adalah 15 items per page