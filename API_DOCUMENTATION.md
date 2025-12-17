# SIMONTA BENCANA API Documentation

## 🚀 **IMPORTANT: ACCESS TOKEN AUTHENTICATION**

Sistem ini menggunakan **Access Token Authentication** dengan Laravel Sanctum untuk keamanan dan performa yang lebih baik.

### 🎯 **Key Features:**

- ✅ **ACCESS TOKEN AUTHENTICATION** - Menggunakan Laravel Sanctum API Tokens
- ✅ **DYNAMIC TOKENS** - Token dibuat secara otomatis saat login
- ✅ **ROLE-BASED ACCESS** - Admin, PetugasBPBD, OperatorDesa, Warga
- ✅ **CLEAN CODE ARCHITECTURE** - Service Layer pattern
- ✅ **BAHASA INDONESIA** - Pesan error dan validation dalam Bahasa Indonesia

---

## 📚 **API Documentation**

### **Swagger UI:**
👉 `http://localhost:8000/api/documentation`

### **API Endpoint:**
👉 `http://localhost:8000`

---

## 🔐 **Authentication Flow dengan Access Token**

### **1. Registrasi User**
```bash
POST /api/auth/register
Content-Type: application/json

{
    "nama": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "Warga",
    "no_telepon": "08123456789",
    "alamat": "Jl. Contoh No. 123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registrasi berhasil",
    "data": {
        "id": 1,
        "nama": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "role": "Warga",
        "no_telepon": "08123456789",
        "alamat": "Jl. Contoh No. 123",
        "created_at": "2025-12-16T00:14:08.000000Z"
    }
}
```

### **2. Login (Dapatkan Access Token)**
```bash
POST /api/auth/login
Content-Type: application/json

{
    "username": "johndoe",
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
            "nama": "John Doe",
            "username": "johndoe",
            "email": "john@example.com",
            "role": "Warga",
            "role_label": "Warga",
            "no_telepon": "08123456789",
            "alamat": "Jl. Contoh No. 123",
            "id_desa": null,
            "desa": null
        },
        "token": "1|abc123def456token",
        "token_type": "Bearer",
        "expires_at": "2026-12-16T00:14:08.000000Z"
    }
}
```

**🔥 PENTING:** Response mengembalikan **access token** yang harus disimpan dan digunakan untuk mengakses protected endpoints.

### **3. Access Protected Endpoint (Dengan Access Token)**
```bash
GET /api/auth/me
Authorization: Bearer 1|abc123def456token
```

**Response:**
```json
{
    "success": true,
    "message": "Data user berhasil diambil",
    "data": {
        "id": 1,
        "nama": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "role": "Warga",
        "role_label": "Warga",
        "no_telepon": "08123456789",
        "alamat": "Jl. Contoh No. 123",
        "id_desa": null,
        "desa": null,
        "tokens_count": 1,
        "last_login_at": "2025-12-16T00:14:08.000000Z"
    }
}
```

### **4. Logout (Revoke Current Token)**
```bash
POST /api/auth/logout
Authorization: Bearer 1|abc123def456token
```

**Response:**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

### **5. Token Management**

#### **Get All User Tokens**
```bash
GET /api/auth/tokens
Authorization: Bearer 1|abc123def456token
```

#### **Logout from All Devices**
```bash
POST /api/auth/logout-all
Authorization: Bearer 1|abc123def456token
```

#### **Revoke Specific Token**
```bash
DELETE /api/auth/tokens/{tokenId}
Authorization: Bearer 1|abc123def456token
```

---

## 🛡️ **Role-Based Access Control**

Sistem menggunakan **access token** untuk memeriksa role dan otorisasi.

### **Admin Only Endpoints:**
- `GET /api/users` - List all users
- `POST /api/users` - Create user
- `GET /api/users/statistics` - User statistics
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user
- `GET /api/users/{id}` - Get specific user

### **All Authenticated Users:**
- `GET /api/auth/me` - Get current user info
- `GET /api/users/profile` - Get user profile
- `PUT /api/users/profile` - Update profile
- `GET /api/auth/tokens` - Get all tokens
- `POST /api/auth/logout` - Logout current token
- `POST /api/auth/logout-all` - Logout all tokens

### **Public Endpoints (No Auth):**
- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Register
- `GET /api/auth/roles` - Get available roles

---

## 📋 **Available Roles**

```json
{
    "Admin": "Administrator",
    "PetugasBPBD": "Petugas BPBD",
    "OperatorDesa": "Operator Desa",
    "Warga": "Warga"
}
```

---

## 🔧 **How to Use (Example with Curl)**

### **1. Register New User**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "nama":"Test User",
    "username":"testuser",
    "email":"test@example.com",
    "password":"password123",
    "password_confirmation":"password123",
    "role":"Warga",
    "no_telepon":"08123456789",
    "alamat":"Alamat Test"
  }'
```

### **2. Login & Save Token**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "username":"testuser",
    "password":"password123"
  }'
```

### **3. Access Protected Endpoint**
```bash
TOKEN="1|abc123def456token" # Token dari response login

curl -X GET http://localhost:8000/api/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}"
```

### **4. Access Admin Only Endpoint**
```bash
TOKEN="1|abc123def456token" # Admin token

curl -X GET http://localhost:8000/api/users/statistics \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}"
```

### **5. Logout**
```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}"
```

---

## ⚠️ **Error Responses (Bahasa Indonesia)**

### **Validation Error (422)**
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "username": "Username wajib diisi",
        "email": "Email sudah digunakan"
    }
}
```

### **Authentication Error (401)**
```json
{
    "success": false,
    "message": "Token tidak valid atau tidak ditemukan",
    "code": "NO_AUTHENTICATED_USER"
}
```

### **Authorization Error (403)**
```json
{
    "success": false,
    "message": "Anda tidak memiliki izin untuk mengakses resource ini",
    "required_roles": ["Admin"],
    "user_role": "Warga",
    "code": "INSUFFICIENT_PERMISSIONS"
}
```

---

## 🎯 **Key Technical Points**

1. **LARAVEL SANCTUM** - Menggunakan API token authentication untuk keamanan tinggi
2. **DYNAMIC TOKENS** - Token dibuat dengan nama unik dan timestamp
3. **BEARER TOKEN** - Format Authorization header: `Bearer {token}`
4. **SERVICE LAYER** - Clean code dengan AuthService untuk logic terpusat
5. **ROLE-BASED** - Middleware untuk cek role dan permission
6. **TOKEN MANAGEMENT** - Fitur lengkap untuk manage multiple tokens
7. **SERVER-SIDE** - Token disimpan dan divalidasi di server
8. **SECURE HASHING** - Password di-hash dengan Bcrypt

---

## 🚀 **Running the Application**

```bash
# Start server
cd backend
php artisan serve --host=0.0.0.0 --port=8000

# Access API Documentation
http://localhost:8000/api/documentation

# Access API
http://localhost:8000/api
```

---

## 📞 **Support**

- **Email:** dev@simonta.id
- **Documentation:** http://localhost:8000/api/documentation

---

**🔥 REMEMBER: GUNAKAN ACCESS TOKEN AUTHENTICATION UNTUK KEAMANAN YANG LEBIH BAIK!**