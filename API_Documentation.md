# SIMONTA BENCANA API Documentation

## Overview
RESTful API untuk Aplikasi SIMONTA Bencana dengan Multi-Role Authentication menggunakan JWT.

## Base URL
```
http://localhost:8000/api
```

## Authentication
API menggunakan JWT (JSON Web Token) untuk autentikasi. Setelah login, Anda akan mendapatkan token yang harus disertakan di header untuk mengakses endpoint yang dilindungi.

**Authorization Header:**
```
Authorization: Bearer <your_jwt_token>
```

## Roles
- **Admin**: Pengelolaan master data, monitoring sistem
- **PetugasBPBD**: Penanganan laporan bencana, update status
- **OperatorDesa**: Verifikasi laporan, monitoring wilayah
- **Warga**: Melapor bencana, melihat informasi

## Endpoints

### Public Endpoints

#### 1. Register User
```
POST /auth/register
```

**Request Body:**
```json
{
  "nama": "Nama User",
  "username": "username",
  "password": "password123",
  "role": "Admin|PetugasBPBD|OperatorDesa|Warga",
  "email": "email@example.com",
  "no_telepon": "08123456789",
  "alamat": "Alamat lengkap",
  "id_desa": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "id": 1,
    "nama": "Nama User",
    "username": "username",
    "role": "Admin",
    "email": "email@example.com"
  }
}
```

#### 2. Login
```
POST /auth/login
```

**Request Body:**
```json
{
  "username": "username",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "nama": "Nama User",
      "username": "username",
      "role": "Admin",
      "email": "email@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### Protected Endpoints (Requires JWT Token)

#### Authentication Endpoints

#### 3. Logout
```
POST /auth/logout
Authorization: Bearer <token>
```

#### 4. Refresh Token
```
POST /auth/refresh
Authorization: Bearer <token>
```

#### 5. Get Profile
```
GET /auth/profile
Authorization: Bearer <token>
```

#### Dashboard Endpoints

#### 6. Dashboard
```
GET /dashboard
Authorization: Bearer <token>
```

### Role-Based Endpoints

#### Admin Only

#### 7. Manage Users
```
GET    /admin/users          - List users
POST   /admin/users          - Create user
PUT    /admin/users/{id}     - Update user
DELETE /admin/users/{id}     - Delete user
```

#### 8. Disaster Categories
```
GET /admin/disaster-categories
```

#### 9. Desa Management
```
GET    /admin/desa          - List all desa with pagination
POST   /admin/desa          - Create new desa
GET    /admin/desa/{id}     - Get desa details
PUT    /admin/desa/{id}     - Update desa
DELETE /admin/desa/{id}     - Delete desa
GET    /admin/desa-statistics - Desa statistics
```

#### 10. Regions Management
```
GET /admin/regions
```

#### 11. System Monitoring
```
GET /admin/system-monitoring
```

#### Multi-Role (Admin, PetugasBPBD, OperatorDesa)

#### 12. Desa Information
```
GET /desa                  - List desa (with filters)
GET /desa/{desa}           - Get desa details
GET /desa-list/kecamatan   - Get kecamatan list
GET /desa-list/kabupaten   - Get kabupaten list
GET /desa-statistics        - Desa statistics
```

#### Petugas BPBD Only

#### 13. Reports Management
```
GET  /bpbd/reports              - List reports
GET  /bpbd/reports/{id}         - Report details
POST /bpbd/reports/{id}/response - Create response
PUT  /bpbd/responses/{id}       - Update response
```

#### 12. Statistics
```
GET /bpbd/statistics
```

#### 13. Notifications
```
POST /bpbd/notifications
```

#### Operator Desa Only

#### 14. Village Reports
```
GET  /operator/reports           - Village reports
POST /operator/reports/{id}/verify - Verify report
POST /operator/reports/{id}/monitor - Create monitoring
```

#### 15. Evacuation Sites
```
GET  /operator/evacuation-sites
POST /operator/evacuation-sites
```

#### Warga (Citizen) Only

#### 16. Public Information
```
GET /citizen/disaster-info      - Disaster information
GET /citizen/evacuation-info    - Evacuation information
```

#### Multi-Role (Admin, PetugasBPBD, OperatorDesa)

#### 17. Reports Access
```
GET /reports         - List reports
GET /reports/{id}    - Report details
```

#### Multi-Role (Warga, OperatorDesa)

#### 18. Create & View Reports
```
POST /reports        - Create disaster report
GET  /my-reports     - My reports
```

### Desa Management API Details

#### 20. Create Desa
```
POST /admin/desa
Authorization: Bearer <token>
Content-Type: application/json

{
  "nama_desa": "Desa Sukamaju",
  "kecamatan": "Kecamatan Makmur",
  "kabupaten": "Kabupaten Sejahtera"
}
```

#### 21. List Desa with Filters
```
GET /desa?search=sukamaju&kecamatan=Makmur&per_page=10
Authorization: Bearer <token>
```

#### 22. Desa Statistics
```
GET /admin/desa-statistics
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "message": "Statistik desa berhasil diambil",
  "data": {
    "total_desa": 10,
    "total_kecamatan": 5,
    "total_kabupaten": 2,
    "desa_terbanyak_pengguna": {...},
    "kecamatan_terbanyak_desa": {...}
  }
}
```

### Test Endpoint

#### 23. API Health Check
```
GET /test
```

**Response:**
```json
{
  "success": true,
  "message": "SIMONTA BENCANA API is running",
  "version": "1.0.0",
  "timestamp": "2025-12-08T15:34:33.088911Z"
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Token tidak ditemukan"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Akses ditolak. Role tidak sesuai.",
  "required_roles": ["Admin"],
  "user_role": "Warga"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "username": ["Username harus diisi"],
    "password": ["Password minimal 6 karakter"]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server"
}
```

## Features Implemented

### ✅ Authentication
- JWT Token-based authentication
- Multi-role user management
- Token refresh mechanism
- Secure password hashing

### ✅ Authorization
- Role-based access control
- Middleware for role checking
- Protected routes by role

### ✅ Activity Logging
- Complete audit trail
- Request/response logging
- IP address tracking
- Device information logging

### ✅ Database Relations
- User management with roles
- Disaster categories
- Location hierarchy (desa)
- Report and response tracking

### ✅ Desa Management
- CRUD operations for desa
- Role-based access control (Admin, OperatorDesa)
- Advanced filtering and search
- Pagination support
- Statistics and analytics
- Data validation and error handling
- Foreign key constraints with pengguna

## Usage Examples

### Register and Login Flow
```bash
# 1. Register new user
curl -X POST "http://localhost:8000/api/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Admin SIMONTA",
    "username": "admin",
    "password": "password123",
    "role": "Admin"
  }'

# 2. Login
curl -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'

# 3. Access protected endpoint
curl -X GET "http://localhost:8000/api/dashboard" \
  -H "Authorization: Bearer <your_token>"
```

## Security Features

- ✅ JWT Token authentication
- ✅ Password hashing with bcrypt
- ✅ Role-based access control
- ✅ Request validation
- ✅ SQL injection protection via Eloquent ORM
- ✅ Activity logging for audit trail

## Next Steps

- Implement business logic controllers (Report, Dashboard, etc.)
- Add file upload handling for photos
- Implement FCM push notifications
- BMKG API integration
- Frontend application (Flutter + PHP Native MVC)

## Technologies Used

- **Backend**: Laravel 12.41.1 (PHP)
- **Authentication**: JWT (Firebase)
- **Database**: MySQL with Eloquent ORM
- **API Documentation**: Markdown
- **Security**: Bcrypt, Role-based Middleware