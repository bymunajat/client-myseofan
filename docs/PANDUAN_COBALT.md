# Panduan Setup Cobalt API (Untuk Pemula)

Panduan ini dibuat khusus agar Anda yang baru pertama kali menggunakan VPS bisa mengikutinya dengan mudah sampai sukses.

## Persiapan
- Pastikan Anda sudah menyewa **VPS Ubuntu 22.04** (contoh: di DigitalOcean/Vultr/Contabo).
- Siapkan **IP Address** VPS Anda (misal: `103.20.10.5`).
- Siapkan **Password Root** VPS Anda.

---

## Langkah 1: Masuk ke VPS (Login)

1.  Buka terminal di komputer Anda (PowerShell di Windows, atau Terminal di Mac).
2.  Ketik perintah login:
    ```bash
    ssh root@103.20.10.5
    ```
    *(Ganti `103.20.10.5` dengan IP VPS Anda)*
3.  Ketik **yes** jika ditanya "Are you sure..." lalu Enter.
4.  Masukkan **Password VPS** Anda (ketikan password tidak akan muncul di layar, itu normal. Ketik saja lalu Enter).

---

## Langkah 2: Install Docker (Mesin Penggerak)

Copy perintah di bawah ini satu per satu dan Paste ke terminal VPS (biasanya klik kanan mouse untuk paste):

```bash
# 1. Update daftar aplikasi di VPS
sudo apt update

# 2. Install Docker
sudo apt install docker.io -y

# 3. Install Plugin Docker Compose
sudo apt install docker-compose-plugin -y

# 4. Nyalakan Docker
sudo systemctl start docker
sudo systemctl enable docker
```

---

## Langkah 3: Membuat Folder untuk Cobalt

Kita buat "kamar" khusus untuk Cobalt agar rapi.

```bash
# Buat folder
mkdir -p /opt/cobalt

# Masuk ke folder itu
cd /opt/cobalt
```

---

## Langkah 4: Membuat File Setting (docker-compose.yml)

Kita akan membuat file setting di dalam VPS.

1.  Ketik perintah ini untuk membuka text editor:
    ```bash
    nano docker-compose.yml
    ```

2.  Copy kode di bawah ini:
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
          # Ganti dengan domain website Anda nanti (penting!)
          - API_URL=https://myseofan.com
    ```

3.  Paste ke jendela terminal tadi (klik kanan).
4.  Simpan dengan cara tekan tombol keyboard:
    -   `Ctrl` + `O` (Lalu tekan `Enter`)
    -   `Ctrl` + `X` (Untuk keluar)

---

## Langkah 5: Jalankan Cobalt!

Sekarang saatnya menghidupkan mesin.

```bash
sudo docker compose up -d
```
*(Tunggu sebentar, VPS akan mendownload Cobalt dari internet...)*

Kalau sudah selesai, cek apakah sudah hidup dengan perintah:
```bash
sudo docker ps
```
Jika muncul tulisan `cobalt-api` dan status `Up`, berarti **SUKSES!** 🎉

Cobalt sekarang sudah jalan di `localhost:9000` di dalam VPS Anda. L lanjut ke panduan setup Website.
