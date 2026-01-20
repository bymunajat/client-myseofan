# Panduan Setup Aplikasi Web (MySeoFan)

Panduan ini adalah langkah lanjutan setelah Anda menyiapkan **Cobalt API** (lihat `PANDUAN_COBALT.md`). Dokumen ini fokus agar aplikasi web berjalan sempurna di VPS.

## 1. Install Web Server & PHP
MySeoFan berjalan dengan Apache dan PHP.

```bash
# Update
sudo apt update

# Install Apache & PHP 8.x + Ekstensi
sudo apt install apache2 php libapache2-mod-php php-sqlite3 php-curl php-xml php-mbstring php-zip unzip -y

# Aktifkan Mod Recall (Wajib untuk .htaccess)
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 2. Upload File Project
Taruh file project Anda di `/var/www/html/myseofan`.

```bash
# Contoh menggunakan git (rekomendasi)
cd /var/www/html
sudo git clone https://github.com/username-anda/myseofan.git myseofan
```

## 3. Setup Permission (Wajib!)
Agar database SQLite dan Logs bisa ditulis oleh server, atur hak aksesnya.

```bash
cd /var/www/html/myseofan

# Berikan kepemilikan ke user Apache (www-data)
sudo chown -R www-data:www-data .

# Pastikan folder database dan scripts writeable (jika perlu)
sudo chmod -R 775 database
sudo chmod -R 775 admin
```

## 4. Install Vendor (Composer)
Masuk ke folder project dan install library PHP.

```bash
# Install Composer dulu jika belum
sudo apt install composer -y

# Install dependensi project
cd /var/www/html/myseofan
composer install --no-dev --optimize-autoloader
```

## 5. Setting Virtual Host (Domain)
Agar domain Anda (misal: `myseofan.com`) mengarah ke folder yang benar.

```bash
sudo nano /etc/apache2/sites-available/myseofan.conf
```

Isi file tersebut:
```apache
<VirtualHost *:80>
    ServerName myseofan.com
    ServerAlias www.myseofan.com
    
    # Arahkan langsung ke folder project
    DocumentRoot /var/www/html/myseofan
    
    <Directory /var/www/html/myseofan>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/myseofan_error.log
    CustomLog ${APACHE_LOG_DIR}/myseofan_access.log combined
</VirtualHost>
```

Aktifkan konfigurasi:
```bash
sudo a2ensite myseofan
sudo systemctl reload apache2
```

## 6. Pasang SSL (HTTPS)
Agar website aman (Gembok Hijau) dan SEO bagus.

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d myseofan.com -d www.myseofan.com
```

## 7. Cek Konfigurasi Akhir (`config.php`)
Buka file config di VPS:
```bash
sudo nano /var/www/html/myseofan/config.php
```

Pastikan isinya sesuai:
```php
// Jika Cobalt ada di VPS yang sama via Docker
define('COBALT_API_URL', 'http://localhost:9000'); 

// Matikan error untuk production
error_reporting(0);
```

## 8. Verifikasi "Sempurna"
Checklist agar berjalan 100% smooth:
1.  Buka web `https://myseofan.com` -> Harus load cepat.
2.  Coba download video Instagram -> Harus sukses (artinya koneksi ke Cobalt lancar).
3.  Coba akses `https://myseofan.com/scripts/` -> Harus **403 Forbidden** (Security OK).

**Selesai!** Website Anda sudah production-ready.
