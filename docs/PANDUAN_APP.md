# Panduan Setup Website MySeoFan (Untuk Pemula)

Setelah Cobalt (backend) berhasil jalan, sekarang kita setup tampilan websitenya (frontend).

---

## Langkah 1: Install Web Server (Apache & PHP)

Copy paste perintah ini di terminal VPS Anda:

```bash
# 1. Install semua software pendukung
sudo apt install apache2 php libapache2-mod-php php-sqlite3 php-curl php-xml php-mbstring php-zip unzip -y

# 2. Aktifkan fitur URL cantik (Rewrite)
sudo a2enmod rewrite

# 3. Restart Apache agar efeknya jalan
sudo systemctl restart apache2
```

---

## Langkah 2: Upload File Website

Karena Anda pemula, cara termudah upload file bukan pakai Git, tapi pakai aplikasi **WinSCP** atau **FileZilla** di komputer Anda.

1.  Download & Install **WinSCP** (Gratis).
2.  Buka WinSCP, masukkan IP VPS, User (`root`), dan Password VPS. Klik **Login**.
3.  Di panel sebelah **Kanan** (itu isi VPS Anda), navigasi ke folder: `/var/www/html`.
4.  Hapus file `index.html` bawaan (jika ada).
5.  Di panel sebelah **Kiri** (itu isi komputer Anda), cari folder project `client-myseofan` Anda.
6.  **Drag & Drop** folder `client-myseofan` dari Kiri ke Kanan.
    *(Atau bisa juga copy semua isinya langsung ke dalam root html)*

Mari asumsikan Anda mengupload folder projectnya menjadi: `/var/www/html/myseofan`.

---

## Langkah 3: Setting Permission (PENTING!)

Kembali ke Terminal VPS (layar hitam). Kita harus memberi izin server untuk membaca file yang baru diupload.

```bash
# Masuk ke folder html
cd /var/www/html

# Berikan hak milik ke 'www-data' (User Apache)
# Ganti 'myseofan' dengan nama folder yang Anda upload tadi
sudo chown -R www-data:www-data myseofan

# Berikan izin tulis untuk database
sudo chmod -R 775 myseofan/database
sudo chmod -R 775 myseofan/admin
```

---

## Langkah 4: Install Library PHP

Kita perlu menginstall "Composer" untuk mendownload library tambahan.

```bash
# 1. Install Composer
sudo apt install composer -y

# 2. Masuk ke folder project
cd /var/www/html/myseofan

# 3. Jalankan install
sudo -u www-data composer install --no-dev
```
*(Tunggu sampai proses selesai)*

---

## Langkah 5: Setting Domain (Virtual Host)

Agar website bisa dibuka pakai nama domain (misal: `myseofan.com`), bukan cuma IP.

1.  Buat file setting baru:
    ```bash
    nano /etc/apache2/sites-available/myseofan.conf
    ```

2.  Copy paste kode ini (GANTI `myseofan.com` dengan domain Anda):
    ```apache
    <VirtualHost *:80>
        ServerName myseofan.com
        ServerAlias www.myseofan.com
        
        # Arahkan ke folder project Anda
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

3.  Simpan: `Ctrl+O` -> Enter -> `Ctrl+X`.

4.  Aktifkan website:
    ```bash
    sudo a2ensite myseofan
    sudo systemctl reload apache2
    ```

---

## Langkah 6: Pasang Gembok Hijau (SSL HTTPS)

Supaya aman dan browser tidak warning.

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d myseofan.com -d www.myseofan.com
```
*(Ikuti petunjuk di layar, pilih opsi Redirect/2 jika ditanya)*

---

## Langkah 7: Edit Config Terakhir

1.  Buka file config:
    ```bash
    nano /var/www/html/myseofan/config.php
    ```

2.  Pastikan baris ini sudah benar:
    ```php
    define('COBALT_API_URL', 'http://localhost:9000');
    // Matikan error
    error_reporting(0);
    ini_set('display_errors', 0);
    ```

3.  Simpan (`Ctrl+O` -> Enter -> `Ctrl+X`).

---

## 🎉 SELESAI!!

Sekarang buka browser dan ketik domain Anda: `https://myseofan.com`.
Website harusnya sudah muncul dan bisa dipakai download video!
