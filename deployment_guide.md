# Panduan Deployment MySeoFan ke VPS (Production)

Dokumen ini berisi panduan lengkap untuk melakukan deployment aplikasi MySeoFan ke server VPS berbasis Linux (Ubuntu 22.04 / 24.04 recommended).

## 1. Persiapan Server (VPS)
Pastikan VPS Anda masih segar (fresh install) untuk menghindari konflik.
- **OS**: Ubuntu 22.04 LTS atau 24.04 LTS
- **RAM**: Minimal 1GB (2GB rekomendasi)
- **Disk**: 10GB+

## 2. Instalasi Software Stack
Login ke VPS Anda via SSH, lalu jalankan perintah berikut secara berurutan.

### Update System
```bash
sudo apt update && sudo apt upgrade -y
```

### Install Apache Web Server
```bash
sudo apt install apache2 -y
```
Aktifkan modul rewrite (penting untuk .htaccess):
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Install PHP 8.1 (atau 8.2) & Ekstensi Penting
Aplikasi ini membutuhkan PHP dan ekstensi SQLite, cURL, XML, dan Zip.
```bash
sudo apt install php libapache2-mod-php php-sqlite3 php-curl php-xml php-mbstring php-zip unzip -y
```
Cek versi PHP:
```bash
php -v
# Pastikan muncul PHP 8.x
```

### Install Composer (PHP Dependency Manager)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 3. Upload & Setup Project

### Opsi A: Menggunakan Git (Recommanded)
Jika kode Anda ada di GitHub/GitLab:
```bash
cd /var/www/html
sudo git clone https://github.com/username/project-anda.git myseofan
```

### Opsi B: Upload Manual (SFTP)
Jika upload manual dari komputer:
1. Upload semua file project ke folder `/var/www/html/myseofan` di VPS menggunakan FileZilla atau WinSCP.

### Setup Dependensi & Permission
Masuk ke folder project:
```bash
cd /var/www/html/myseofan
```
Install library PHP (Google Translate, dll):
```bash
composer install --no-dev --optimize-autoloader
```
**PENTING:** Atur hak akses folder agar bisa menulis database dan log.
```bash
# Ubah kepemilikan ke user web server (www-data)
sudo chown -R www-data:www-data /var/www/html/myseofan

# Pastikan folder database bisa ditulis
sudo chmod -R 775 /var/www/html/myseofan/database
sudo chmod -R 775 /var/www/html/myseofan/admin/logs.txt 2>/dev/null || true
```

## 4. Konfigurasi Virtual Host
Buat konfigurasi agar domain Anda mengarah ke folder project.

Buat file config baru:
```bash
sudo nano /etc/apache2/sites-available/myseofan.conf
```
Isi dengan konfigurasi berikut (ganti `domain-anda.com`):
```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    ServerAlias www.domain-anda.com
    
    DocumentRoot /var/www/html/myseofan
    
    <Directory /var/www/html/myseofan>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
Simpan (Ctrl+O, Enter) dan Keluar (Ctrl+X).

Aktifkan site baru:
```bash
sudo a2ensite myseofan.conf
sudo a2dissite 000-default.conf # Matikan default
sudo systemctl reload apache2
```

## 5. Konfigurasi Aplikasi (PENTING)
Edit file `config.php` untuk production.
```bash
nano /var/www/html/myseofan/config.php
```
Ubah bagian ini:
1.  **COBALT_API_URL**: Ganti `http://localhost:9000` dengan URL API server Cobalt production Anda (misal: `https://api.cobalt.tools` atau server sendiri).
2.  **Error Reporting**: Pastikan tetap mati (`0`) di production.

## 6. Install SSL (HTTPS)
Agar website aman (gembok hijau), gunakan Certbot (Let's Encrypt).
```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d domain-anda.com -d www.domain-anda.com
```
Ikuti instruksi di layar.

## 7. Cek Keamanan Akhir
Coba akses URL berikut di browser untuk memastikan `.htaccess` bekerja:
- `https://domain-anda.com/scripts/seed_full_pages.php` -> Harusnya **403 Forbidden**.
- `https://domain-anda.com/database/myseofan.db` -> Harusnya **403 Forbidden**.

## Selesai!
Website Anda sekarang sudah live di VPS.

## 8. Strategi Git Workflow (Teraman)

Untuk menjaga website tetap aman dan mudah diperbaiki jika ada error, gunakan strategi **3 Branch** ini:

### 1. Main Branch (`main`) - Production
*   **Fungsi**: Kode yang sedang live di website `snapyolo.com`.
*   **Aturan**: **JANGAN PERNAH** commit langsung ke sini. Hanya boleh menerima update (Merge) dari branch `dev`.
*   **Kondisi**: Kode di sini harus 100% stabil dan bebas bug fatal.

### 2. Dev Branch (`dev`) - Staging/Beta
*   **Fungsi**: Tempat mengumpulkan semua fitur baru sebelum dirilis ke `main`.
*   **Aturan**: Coding sehari-hari dilakukan atau digabungkan di sini.
*   **Kondisi**: Boleh ada bug minor, tapi secara umum harus jalan.

### 3. Feature Branch (`feature/...`) - Eksperimen
*   **Fungsi**: Cabang khusus untuk membuat 1 fitur tertentu.
*   **Contoh Nama**: `feature/tambah-login`, `feature/ganti-warna-header`, `fix/tombol-error`.
*   **Aturan**: Bikin branch ini dari `dev`. Kalau fitur sudah selesai dan dites di localhost, baru di-merge ke `dev`.

---
### Alur Kerja (Workflow):
1.  **Mulai**: Buat branch baru dari `dev` (misal: `feature/tombol-baru`).
2.  **Coding**: Edit kode di laptop (Localhost).
3.  **Test**: Pastikan jalan lancar di laptop.
4.  **Merge ke Dev**: Gabungkan `feature/tombol-baru` ke `dev`.
5.  **Merge ke Main**: Kalau di `dev` sudah oke, gabungkan `dev` ke `main`.
6.  **Deploy**: Login ke Server -> `git pull`.
