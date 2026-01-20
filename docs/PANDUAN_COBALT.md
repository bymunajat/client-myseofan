# Panduan Setup Cobalt API (Backend Downloader)

Dokumen ini menjelaskan cara menginstal **Cobalt API** di VPS yang sama dengan aplikasi MySeoFan agar fitur download Instagram berjalan lancar. Cobalt akan dijalankan menggunakan **Docker** di port `9000`.

## 1. Install Docker & Docker Compose
Cobalt membutuhkan Docker. Jalankan perintah ini di terminal VPS Anda:

```bash
# Update repository
sudo apt update

# Install Docker
sudo apt install docker.io -y

# Install Docker Compose Plugin
sudo apt install docker-compose-plugin -y

# Start & Enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Cek apakah Docker sudah berjalan
sudo docker --version
```

## 2. Siapkan Folder Cobalt
Buat folder khusus untuk menyimpan konfigurasi Cobalt.

```bash
mkdir -p /opt/cobalt
cd /opt/cobalt
```

## 3. Buat File `docker-compose.yml`
Kita akan menggunakan Docker Compose agar mudah dikelola. Buat filenya:

```bash
nano docker-compose.yml
```

Salin dan tempel konfigurasi berikut:

```yaml
version: '3.8'

services:
  cobalt-api:
    image: ghcr.io/imputnet/cobalt:latest
    container_name: cobalt-api
    restart: unless-stopped
    ports:
      - "9000:9000"
    environment:
      # URL website utama Anda (untuk CORS/Security)
      - API_URL=https://domain-anda.com
      # Biarkan kosong atau sesuaikan jika Anda punya instance processing sendiri
      - REC_URL=
      # Ganti dengan random string yang aman jika ingin membatasi akses (opsional)
      - API_KEY=
    networks:
      - cobalt-net

networks:
  cobalt-net:
    driver: bridge
```
*(Tekan `Ctrl+O` lalu `Enter` untuk simpan, `Ctrl+X` untuk keluar)*

## 4. Jalankan Cobalt
Jalankan container Cobalt di background:

```bash
sudo docker compose up -d
```

Cek apakah sudah jalan:
```bash
sudo docker ps
```
Anda harusnya melihat `cobalt-api` status `Up` dan port `9000->9000`.

## 5. Tes Koneksi Cobalt
Dari dalam VPS, coba ping API-nya:

```bash
curl http://localhost:9000/api/serverInfo
```
Jika muncul respon JSON (misal: `{"version":"..."}`), berarti Cobalt sukses berjalan!

---

## Integrasi dengan MySeoFan
Karena Cobalt berjalan di `localhost:9000` pada VPS yang sama, Anda **TIDAK PERLU** mengubah `config.php` jika settingnya sudah:

```php
define('COBALT_API_URL', 'http://localhost:9000');
```

Aplikasi PHP (MySeoFan) akan menghubungi Cobalt via jaringan internal server (localhost), sehingga **sangat cepat** dan **lebih aman** karena port 9000 tidak perlu dibuka ke publik (cukup localhost yang akses).

**Selesai!** Backend downloader sudah siap. Lanjutkan ke instalasi aplikasi web (lihat `PANDUAN_APP.md`).
