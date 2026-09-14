# PT. ADK-6 Crew Compliance & Automated Payroll Slip System

System manajemen data crew, pemantauan kepatuhan (*compliance*), pencetakan surat kerja, dan pengiriman otomatis slip gaji PDF via WhatsApp (*WhatsApp Automation Engine*).

---

##  Fitur Utama System

### 1.  Dashboard & Master Data Crew
* **Overview Kepatuhan Crew:** Ringkasan status aktif/nonaktif crew per Rig.
* **Filtering & Live Search AJAX:** Pencarian cepat berdasarkan Rig, Posisi, Crew (A/B/C/D), dan Status tanpa reload halaman.
* **Multi-Rig Assignment:** Hak akses pengguna terisolasi sesuai Rig yang ditugaskan.

### 2. Compliance Monitoring (Kepatuhan Work Readiness)
* **Pemeriksaan MCU (Medical Check Up):** Pemantauan derajat kesehatan dan tanggal kadaluarsa MCU.
* **Masa Berlaku PKWT & Badge:** Notifikasi otomatis untuk kontrak kerja dan badge yang mendekati masa *expired*.
* **Sertifikat Kerja & Keterampilan:** Inventarisasi dan pencatatan sertifikat keselamatan kerja crew.

### 3. Sistem Administrasi & Pencetakan Surat
* Pencetakan surat otomatis dengan format standar perusahaan:
  * Surat Keterangan Asli (SKA) & Keterangan Kerja (SKK)
  * Surat Peringatan (SP) & Berita Acara (BA)
  * Surat Perjanjian Kerja Waktu Tertentu (PKWT) & Pemutusan Hubungan Kerja (PHK)
  * Surat Perintah Kerja (SPK) & Medical Check Up (MCU)

### 4.  Modul Admin Gaji & WhatsApp Automation Engine (`wa_service_v2`)
* **Upload Batch ZIP Slip Gaji:** Pengunggahan ribuan file PDF slip gaji dalam 1 berkas `.zip`.
* **Automatic Name & File Matching:** Sistem otomatis mendeteksi nama file PDF (`NAMA - JABATAN.pdf`) dan memetakan ke nomor WhatsApp karyawan.
* **Dual Login Method:** Dukungan scan **QR Code** dan **Kode Pairing 8-Digit**.
* **Anti-Banned Protection Engine:**
  * *Random Delay Timer:* Jeda acak 12 – 25 detik antar pengiriman pesan.
  * *Batch Rest Controller:* Istirahat otomatis 3 – 5 menit setiap 15 pengiriman pesan.
* **Real-time Status Callback & Polling:** Update status pengiriman (`Pending`, `Terkirim`, `Gagal`) secara otomatis di database MySQL.
* **Fitur Retry Single/Massal:** Pengiriman ulang berkas yang gagal tanpa risiko terkirim ganda (*anti-duplicate check*).

---

##  Teknologi & Stack

* **Backend Web:** PHP (Custom Light MVC Architecture)
* **Database:** MySQL / MariaDB (`tracker_k3` & `apd_system`)
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla & AJAX), Bootstrap 5, FontAwesome
* **Automation Microservice (`wa_service_v2`):**
  * Node.js & Express.js
  * `whatsapp-web.js` + Puppeteer (Headless Chrome)
  * `LocalAuth` untuk persistent session

---

##  Struktur Direktori Proyek

```text
project_kp/
├── app/
│   ├── config/         # Konfigurasi database & environment
│   ├── controllers/    # Logika controller (AdminGaji, Crew, Auth, Surat, dll)
│   ├── helpers/        # Helper cURL WA & Utility
│   ├── models/         # Model database MySQL
│   └── services/       # Layanan pendukung
├── core/               # MVC Core Framework (Controller, Model, Helper)
├── database/           # File SQL setup database
├── public/             # Asset CSS, JS, dan Gambar (kop surat, logo, dll)
├── views/              # Tampilan UI HTML/PHP (Admin Gaji, Crew, Auth, Surat)
├── wa_service_v2/      # Microservice Node.js WhatsApp Engine
│   ├── uploads/        # Berkas ZIP & PDF Slip Gaji per periode
│   ├── whatsapp-session/ # Sesi terenkripsi WhatsApp Web
│   └── server.js       # Node.js API server & WA Worker
├── .htaccess           # URL Rewriting Apache
└── index.php           # Entry Point Aplikasi
```

---

##  Panduan Instalasi & Cara Menjalankan Proyek

### 1. Prasyarat Sistem
* **XAMPP** dengan PHP (v7.4 atau v8.x) & MySQL/MariaDB.
* **Node.js** (v18.x atau yang lebih baru) & **npm**.
* Browser Google Chrome terinstal di komputer.

---

### 2. Langkah Instalasi Database & Web (PHP)

1. **Clone / Salin Proyek:**
   Buka folder `htdocs` XAMPP Anda (`C:\xampp\htdocs\`) dan masukkan folder proyek ini dengan nama `project_kp`.

2. **Import Database:**
   * Buka phpMyAdmin (`http://localhost/phpmyadmin/`).
   * Buat database baru dengan nama `tracker_k3`.
   * Import file SQL yang ada di direktori `database/setup_database.sql`.

3. **Konfigurasi Database:**
   Pastikan konfigurasi di `app/config/database.php` mengarah ke MySQL lokal:
   ```php
   private $host = "localhost";
   private $user = "root";
   private $pass = "";
   private $db   = "tracker_k3";
   ```

---

### 3. Langkah Instalasi WhatsApp Microservice (Node.js)

1. Buka Terminal / Command Prompt dan masuk ke direktori `wa_service_v2`:
   ```bash
   cd C:\xampp\htdocs\project_kp\wa_service_v2
   ```

2. Install dependency Node.js:
   ```bash
   npm install
   ```

3. Jalankan WhatsApp Service:
   ```bash
   node server.js
   ```
   *Service akan berjalan pada `http://localhost:3000`.*

---

### 4. Menjalankan Aplikasi

1. Pastikan **Apache** & **MySQL** di XAMPP Control Panel dalam keadaan **Started**.
2. Pastikan **Node.js WhatsApp Service** (`wa_service_v2`) sedang berjalan di Terminal.
3. Buka browser dan akses aplikasi:
   ```text
   http://localhost/project_kp/auth/login
   ```

---

##  Kredensial Login Default

| Role | Email | Password Default |
| :--- | :--- | :--- |
| **Super Admin** | `admin@gmail.com` | `admin123` |
| **Payroll Admin** | `admin1@gmail.com` | `admin123` |
| **Admin Gaji (WA)** | `payroll@gmail.com` | `payroll123` |

---

##  Alur Penggunaan Modul Admin Gaji

1. **Login** menggunakan akun Admin Gaji (`payroll@gmail.com` / `payroll123`).
2. Masuk ke menu **Upload & Kirim Slip**.
3. **Langkah 1:** Pilih Periode Bulan/Tahun dan Upload File `.zip` yang berisi PDF Slip Gaji.
4. **Langkah 2:** Klik tombol **"Mulai Kirim WA Otomatis"**.
5. Pengiriman akan berjalan secara otomatis di background server. Anda dapat memantau status secara real-time di halaman **Status Pengiriman**.

---

##  Lisensi & Hak Cipta
© 2026 PT. ADK-6 Crew Compliance Team. All Rights Reserved.
