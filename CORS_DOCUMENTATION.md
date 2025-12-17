# CORS Configuration Documentation

## 🌐 **Dynamic CORS untuk SIMONTA BENCANA API**

Backend ini telah dikonfigurasi dengan **CORS (Cross-Origin Resource Sharing) yang dinamis** untuk memungkinkan akses dari berbagai platform dan origins.

---

## 🚀 **Fitur CORS Dinamis**

### ✅ **Allowed Origins yang Fleksibel:**

1. **Local Development**
   - ✅ `localhost:*` (semua port)
   - ✅ `127.0.0.1:*` (semua port)
   - ✅ `192.168.*:*` (local network)
   - ✅ `10.*:*` (local network)
   - ✅ `172.16-31.*:*` (local network)

2. **Development Servers**
   - ✅ `*.ngrok.io` (ngrok tunnels)
   - ✅ `*.xip.io` (xip.io testing)

3. **Production Domains**
   - ✅ `simonta-bencana.com`
   - ✅ `*.simonta-bencana.com`
   - ✅ `simonta.id`
   - ✅ `*.simonta.id`

4. **Mobile Apps**
   - ✅ `exp://*` (Expo React Native)
   - ✅ `simonta://*` (Custom deep links)

5. **Environment-Specific**
   - ✅ `*.local.com` (local environment)
   - ✅ `*.staging.com` (staging environment)

---

## 🔧 **Konfigurasi CORS**

### **1. File Konfigurasi Utama**

**Location:** `config/cors.php`

```php
'allowed_origins' => ['*'], // Allow all origins dynamically

'allowed_origins_patterns' => [
    // Local development
    '/^https?:\/\/localhost:\d+$/',
    '/^https?:\/\/127\.0\.0\.1:\d+$/',

    // Development servers
    '/^https?:\/\/.*\.ngrok\.io$/',
    '/^https?:\/\/.*\.xip\.io:\d*$/',

    // Production domains
    '/^https?:\/\/(.+\.)?simonta-bencana\.com$/',
    '/^https?:\/\/(.+\.)?simonta\.id$/',

    // Mobile apps
    '/^exp:\/\/.*$/',
    '/^simonta:\/\/.*$/',

    // Local network
    '/^https?:\/\/(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.).*:\d*$/',
],
```

### **2. Dynamic CORS Middleware**

**Location:** `app/Http/Middleware/DynamicCors.php`

Middleware ini secara otomatis:
- ✅ Mencek origin dari request
- ✅ Validasi origin dengan rules dinamis
- ✅ Menambahkan CORS headers yang sesuai
- ✅ Handle preflight OPTIONS requests

### **3. Global Middleware Registration**

**Location:** `bootstrap/app.php`

```php
// Apply dynamic CORS middleware globally to all routes
$middleware->append(\App\Http\Middleware\DynamicCors::class);
```

---

## 📋 **Supported HTTP Methods**

```php
'allowed_methods' => [
    'GET',      // Get data
    'POST',     // Create data
    'PUT',      // Update data (full replace)
    'PATCH',    // Update data (partial)
    'DELETE',   // Delete data
    'OPTIONS',  // Preflight requests
],
```

---

## 🔒 **Supported Headers**

### **Allowed Request Headers**
```php
'allowed_headers' => [
    'Content-Type',              // Standard content types
    'Authorization',             // Bearer token authentication
    'X-Requested-With',          // AJAX requests
    'X-CSRF-Token',              // CSRF protection
    'Accept',                    // Accepted content types
    'Origin',                    // Request origin
    'Access-Control-Request-Method', // Preflight
    'Access-Control-Request-Headers', // Preflight
    'X-Device-Platform',          // Device identification
    'X-App-Version',              // App version
    'X-Client-Info',              // Client information
    'X-Mobile-Platform',          // Mobile platform info
],
```

### **Exposed Response Headers**
```php
'exposed_headers' => [
    'X-Total-Count',      // Pagination total
    'X-Per-Page',         // Pagination per page
    'X-Current-Page',     // Pagination current page
    'X-Total-Pages',      // Pagination total pages
    'Content-Disposition', // File downloads
    'X-API-Version',      // API version
    'X-Response-Time',    // Response time
],
```

---

## 🔄 **CORS Flow Process**

### **1. Preflight Request (OPTIONS)**
```
Origin: http://localhost:3000
Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type, Authorization
```

**Response Headers:**
```
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, ...
Access-Control-Max-Age: 86400
Access-Control-Allow-Credentials: true
```

### **2. Actual Request (POST/GET/etc)**
```
Origin: http://localhost:3000
Authorization: Bearer 5|token123
Content-Type: application/json
```

**Response Headers:**
```
Access-Control-Allow-Origin: http://localhost:3000
Vary: Origin
Access-Control-Allow-Credentials: true
Access-Control-Expose-Headers: X-Total-Count, X-Per-Page, ...
```

---

## 🎯 **Environment-Specific Rules**

### **Development Environment (`APP_ENV=local`)**
- ✅ **Allow all origins** - Maximum flexibility for development
- ✅ Local network IPs allowed
- ✅ Development servers allowed
- ✅ Debug headers included

### **Staging Environment (`APP_ENV=staging`)**
- ✅ Production domains allowed
- ✅ Staging domains allowed
- ✅ Development servers allowed
- ✅ Local network IPs allowed

### **Production Environment (`APP_ENV=production`)**
- ✅ Only production domains allowed
- ✅ Mobile app deep links allowed
- ✅ Restricted origins for security

---

## 📱 **Mobile App Integration**

### **React Native (Expo)**
```javascript
// Request headers
const headers = {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`,
    'Origin': 'exp://yourapp', // Expo deep link
    'X-Mobile-Platform': 'expo',
    'X-App-Version': '1.0.0',
};
```

### **Android Native**
```java
// Request headers
Map<String, String> headers = new HashMap<>();
headers.put("Content-Type", "application/json");
headers.put("Authorization", "Bearer " + token);
headers.put("Origin", "simonta://app"); // Custom deep link
headers.put("X-Mobile-Platform", "android");
headers.put("X-App-Version", "1.0.0");
```

### **iOS Native**
```swift
// Request headers
var headers: [String: String] = [:]
headers["Content-Type"] = "application/json"
headers["Authorization"] = "Bearer \(token)"
headers["Origin"] = "simonta://app" // Custom deep link
headers["X-Mobile-Platform"] = "ios"
headers["X-App-Version"] = "1.0.0"
```

---

## 🌍 **Testing CORS**

### **1. Test dengan cURL**
```bash
# Preflight request
curl -X OPTIONS http://localhost:8000/api/auth/login \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type, Authorization" \
  -i

# Actual request
curl -X POST http://localhost:8000/api/auth/login \
  -H "Origin: http://localhost:3000" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-token" \
  -d '{"username":"admin","password":"admin123"}' \
  -i
```

### **2. Test dari Browser**
```javascript
// Test dari browser console
fetch('http://localhost:8000/api/auth/roles', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer your-token'
    }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### **3. Test dari Different Origins**
```bash
# Test dari different origins
origins=(
    "http://localhost:3000"
    "http://localhost:5173"
    "https://yourapp.ngrok.io"
    "http://192.168.1.100:3000"
)

for origin in "${origins[@]}"; do
    echo "Testing from: $origin"
    curl -X GET http://localhost:8000/api/auth/roles \
      -H "Origin: $origin" \
      -H "Accept: application/json" \
      -i
    echo "----------------------------------------"
done
```

---

## 🔧 **Troubleshooting CORS**

### **1. Origin Not Allowed**
**Error:** `Origin http://localhost:3000 is not allowed by Access-Control-Allow-Origin`

**Solution:**
- Check if origin matches any pattern in `allowed_origins_patterns`
- Add origin to `allowed_origins` array
- Verify environment variables

### **2. Credentials Not Allowed**
**Error:** `Credentials flag is 'true', but the 'Access-Control-Allow-Origin' header is '*'`

**Solution:**
- Origin harus spesifik, tidak boleh `*`
- Pastikan `supports_credentials` set ke `true`
- Check middleware configuration

### **3. Headers Not Allowed**
**Error:** `Request header field Authorization is not allowed by Access-Control-Allow-Headers`

**Solution:**
- Add header to `allowed_headers` array
- Check case sensitivity
- Verify preflight request

### **4. Method Not Allowed**
**Error:** `Method POST is not allowed by Access-Control-Allow-Methods`

**Solution:**
- Add method to `allowed_methods` array
- Check HTTP method spelling
- Verify preflight request

---

## 📝 **Custom CORS Rules**

### **Menambah Custom Domain**
```php
// config/cors.php
'allowed_origins_patterns' => [
    // Tambah custom pattern
    '/^https?:\/\/(.+\.)?yourdomain\.com$/',
],
```

### **Menggunakan Environment Variables**
```php
// config/cors.php
$customDomains = explode(',', env('CORS_ALLOWED_DOMAINS', ''));
$allowed_origins = array_merge($allowed_origins, $customDomains);
```

### **Conditional CORS berdasarkan Path**
```php
// app/Http/Middleware/DynamicCors.php
protected function isOriginAllowed($origin, $request = null)
{
    $path = $request?->path() ?? '';

    // Special rules untuk specific paths
    if (str_contains($path, 'admin')) {
        return $this->isAdminOrigin($origin);
    }

    if (str_contains($path, 'public')) {
        return true; // Public API allows all origins
    }

    return $this->isStandardOrigin($origin);
}
```

---

## 🎉 **Benefits of Dynamic CORS**

1. **🔒 Secure:** Hanya origins yang diizinkan yang bisa mengakses API
2. **🚀 Flexible:** Mudah menambah/remove origins tanpa restart server
3. **📱 Multi-Platform:** Support web, mobile, dan desktop apps
4. **🌍 Environment-Aware:** Rules berbeda untuk development/staging/production
5. **🔧 Maintainable:** Konfigurasi terpusat dan mudah dikelola
6. **📊 Debug-Friendly:** Headers lengkap untuk debugging

---

## 📞 **Support**

Jika Anda mengalami masalah dengan CORS:

1. **Check browser console** untuk pesan error detail
2. **Verify request headers** dengan developer tools
3. **Test dengan cURL** untuk isolasi masalah
4. **Check environment variables** untuk konfigurasi

**Contact:** dev@simonta.id