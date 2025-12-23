# DOKUMEN PERANCANGAN TEKNIS APLIKASI SIMONTA BENCANA

Dokumen ini disusun berdasarkan rancang bangun (design) yang terdapat dalam file "Tahap Perancangan.pdf" serta mematuhi spesifikasi teknis dan catatan penting yang ditetapkan.

---

## 1. BACKEND (LARAVEL REST API)

Backend akan berfungsi sebagai pusat data dan logika bisnis utama (Server Side) yang mengimplementasikan **RESTful API** menggunakan *Laravel* dengan struktur **Microservices** dan **SOA** untuk integrasi eksternal.

### 1.1 Arsitektur dan Teknologi
- **Framework**: Laravel (PHP)
- **Struktur**: RESTful API
- **Database**: MySQL/PostgreSQL
- **Autentikasi**: JWT (JSON Web Token) untuk Multi-role Authentication.
- **Pencatatan Aktivitas (Log Activity)**: Implementasi *middleware logger* pada tiap endpoint API. Log disimpan dalam tabel `log_activity`.

### 1.2 Layanan (Microservices & SOA)
Backend akan menyediakan layanan utama yang mendukung arsitektur Service-Oriented Architecture (SOA) dan Microservices:

- **Auth Service**: Menangani registrasi, login, token, dan manajemen hak akses pengguna (Admin, Petugas BPBD, Operator Desa, Warga).
- **Report Service**: Menangani laporan masuk, verifikasi, dan pembaruan status laporan.
- **Notification Service**: Mengirim notifikasi via FCM (Firebase Cloud Messaging) untuk *update status laporan* dan *informasi darurat*.
- **Map Service**: Menyediakan data koordinat lokasi bencana dan evakuasi untuk pemetaan.
- **Log Service**: Mencatat semua aktivitas pengguna dan sistem.
- **Dashboard Service**: Menyediakan data statistik laporan dan status tanggapan untuk tampilan dashboard.
- **External Service Adapter (SOA)**: Mengatur sinkronisasi data dengan sistem eksternal.

### 1.3 Integrasi Eksternal (BMKG)
Backend harus mengintegrasikan layanan dengan BMKG melalui skema SOA/REST untuk **Sinkronisasi data peringatan dini**. Integrasi ini dilakukan dengan mempelajari **OPEN API BMKG** dari tautan yang diberikan ("https://data.bmkg.go.id/") dan mengimplementasikannya pada layanan *External Service Adapter* atau *BMKG Integration*.

**Catatan Penting:** Jangan pernah membuat data dummy, termasuk untuk percobaan BMKG API. Gunakan data *live* BMKG saat API siap.

---

## 2. ANDROID (FLUTTER)

Aplikasi mobile dibuat menggunakan *Flutter* (Client Side) dan dirancang agar navigasi dari layar ke layar sesuai dengan hak akses pengguna (Multi-role Authentication).

### 2.1 Teknologi dan Fitur
- **Framework**: Flutter (untuk Android Mobile)
- **Komunikasi**: Akses data melalui RESTful API Backend Laravel.
- **Fitur Kunci**:
  - **GPS Integration**: Mengambil lokasi otomatis pengguna saat melaporkan bencana.
  - **Camera Access**: Upload bukti visual (foto/video) saat pelaporan.
  - **Push Notification**: Menerima notifikasi status laporan dan informasi bencana.
  - **Offline Support**: Implementasi penyimpanan lokal laporan sebelum dikirim saat koneksi tersedia.

### 2.2 Hak Akses dan Fungsionalitas Aktor

| Aktor | Halaman Utama yang Diakses | Fungsionalitas Utama (Use Case & Activity) |
| :--- | :--- | :--- |
| **Warga** | Beranda, Informasi, Laporan | Login/Registrasi, **Melaporkan bencana** (Unggah Lokasi + Foto), Melihat status laporan, Menerima notifikasi bencana, Melihat informasi evakuasi. |
| **Operator Desa** | Dashboard / Monitoring | Login/Registrasi, **Verifikasi laporan warga**, Menambahkan informasi lokasi evakuasi, Monitoring laporan dari wilayahnya, Koordinasi dengan petugas BPBD. |
| **Petugas BPBD** | Dashboard / Monitoring | Login/Registrasi, Menerima dan menangani laporan terverifikasi, **Update status penangahan bencana**, Monitoring, data dan statistik bencana, Mengatur distribusi bantuan. |
| **Admin** | Dashboard | Login/Registrasi, **Kelola data pengguna**, Kelola kategori bencana, Kelola data wilayah, Monitoring keseluruhan sistem, Mengirim notifikasi ke publik. |

---

## 3. WEB (PHP NATIVE MVC)

Aplikasi Web (Client Side) akan dikembangkan menggunakan *PHP Native* dengan struktur **MVC (Model-View-Controller)** dan berfungsi sebagai *Dashboard/Monitoring* untuk **Admin, Petugas BPBD, dan Operator Desa**.

### 3.1 Teknologi dan Struktur
- **Framework**: PHP Native (Tidak menggunakan Framework)
- **Struktur**: MVC (Model-View-Controller)
- **Komunikasi**: Mengakses data melalui RESTful API Backend Laravel (Controller berkomunikasi dengan Model/API).
- **Tampilan**: HTML5, CSS3, JavaScript (untuk tampilan Web Responsive).

### 3.2 Hak Akses dan Fungsionalitas Aktor (Web Interface)

Antarmuka web akan fokus pada fungsi pengelolaan, monitoring, dan tindak lanjut yang kompleks, terutama bagi pengguna yang memiliki hak akses lebih tinggi.

| Aktor | Fokus Fungsionalitas (Use Case) | Catatan Khusus |
| :--- | :--- | :--- |
| **Admin** | **Pengelolaan Master Data dan Monitoring Sistem** | Dashboard utama menampilkan statistik laporan. Melakukan pengelolaan penuh: Kelola data pengguna, Kelola kategori bencana, Kelola data wilayah, Monitoring keseluruhan sistem, Hapus laporan (jika tidak valid). |
| **Petugas BPBD** | **Tindak Lanjut dan Monitoring Bencana** | Menerima dan menangani laporan terverifikasi, Melakukan **Update status penangahan bencana**, Monitoring, data, dan statistik bencana, Mengatur distribusi bantuan. |
| **Operator Desa** | **Verifikasi dan Koordinasi Wilayah** | **Verifikasi laporan warga**, Menambahkan informasi lokasi evakuasi, Monitoring laporan dari wilayahnya, Koordinasi dengan petugas BPBD. |

---

## PROGRES.MD

Dokumen ini akan mencatat semua kemajuan, *issue* yang ditemukan, dan keputusan penting selama proses pengembangan, untuk memastikan transparansi dan pelacakan pekerjaan.

| Tanggal | Modul/Komponen | Deskripsi Progres | Status |
| :--- | :--- | :--- | :--- |
| [Tanggal] | Backend - Auth Service | Selesai pembuatan endpoint Login dan Registrasi untuk 4 Role (Admin, Petugas, Operator, Warga). | Selesai |
| [Tanggal] | Android - Halaman Kirim Laporan | Selesai perancangan UI/UX Halaman Kirim Laporan dengan integrasi input Lokasi, Jenis Bencana, Deskripsi, dan Camera Access. | Selesai |
| [Tanggal] | Backend - Report Service | Selesai endpoint `buatLaporan()` dan Logic Simpan Laporan ke Database. | Selesai |

### Catatan Tambahan untuk Tim Pengembangan Backend:
1. **Semua progres dilaporkan dalam file PROGRES.md.**
2. **Dilarang keras membuat data dummy, walaupun untuk tujuan percobaan unit testing. Gunakan data live/test case yang valid.**
3. **Android dikembangkan menggunakan Flutter.**
4. **Web dikembangkan menggunakan PHP Native dengan struktur MVC.**
5. **Backend dikembangkan menggunakan Laravel dengan struktur REST API.**
6. **Pastikan integrasi dengan BMKG API telah diuji fungsionalitasnya untuk peringatan dini.**
7. **Implementasikan function GET ALL, GET BY ID, POST, PUT, DELETE.**
8. **Kompleksitasnya harus memadai dan disesuaikan dengan alur yang dibutuhkan.**
9. **Dalam pengodean (atau penulisan kode), prioritaskan clean code untuk meminimalkan galat (error) yang tidak diinginkan**
10. **Dalam pengodean, saya menghendaki seluruhnya bersifat dinamis dan mengikuti prinsip clean code, tanpa komentar di dalam kode, tidak mengandung komponen semi-statis, dan semuanya diizinkan untuk dinamis.*
11. **Pastikan setiap data yang memiliki kunci asing (foreign key) dengan data lain dibuat agar berelasi dan dapat diakses bersamaan.*

### Catatan Tambahan untuk Tim Pengembangan Website:
1. **Semua progres dilaporkan dalam file PROGRES WEBSITE.md.**
2. **Dilarang keras membuat data dummy, walaupun untuk tujuan percobaan unit testing. Gunakan data live/test case yang valid.**
3. **Android dikembangkan menggunakan Flutter.**
4. **Web dikembangkan menggunakan PHP Native dengan struktur MVC.**
5. **Backend dikembangkan menggunakan Laravel dengan struktur REST API.**
6. **Pastikan integrasi dengan BMKG API telah diuji fungsionalitasnya untuk peringatan dini.**
7. **Implementasikan function GET ALL, GET BY ID, POST, PUT, DELETE.**
8. **Kompleksitasnya harus memadai dan disesuaikan dengan alur yang dibutuhkan.**
9. **Dalam pengodean (atau penulisan kode), prioritaskan clean code untuk meminimalkan galat (error) yang tidak diinginkan**
10. **Dalam pengodean, saya menghendaki seluruhnya bersifat dinamis dan mengikuti prinsip clean code, tanpa komentar di dalam kode, tidak mengandung komponen semi-statis, dan semuanya diizinkan untuk dinamis.*
11. **Pastikan setiap data yang memiliki kunci asing (foreign key) dengan data lain dibuat agar berelasi dan dapat diakses bersamaan.*
11. **Semuanya data harus Dynamic Content Management ditulis dengan clean code dan dinamis.*