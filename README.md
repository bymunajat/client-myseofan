# MySeoFan - Premium Instagram Downloader

MySeoFan is a high-speed, privacy-focused media downloader for Instagram. It allows users to download Videos, Photos, Reels, IGTV, and Carousel albums anonymously without logging in.

![Project Banner](assets/images/banner_placeholder.png) 
*(Note: Replace `banner_placeholder.png` with an actual screenshot if available)*

## 🌟 Features
*   **No Login Required**: Completely anonymous usage.
*   **Multi-Format Support**: Downloads Videos, Photos, Reels, IGTV, and Carousels.
*   **High Performance**: Powered by Cobalt API for lightning-fast processing.
*   **Premium UI**: Aesthetically pleasing design using **Tailwind CSS** and **Glassmorphism**.
*   **Multi-Language**: Built-in localization support (English, Indonesian, etc.).
*   **SEO Optimized**: Semantic HTML and dynamic meta tags.

## 🛠️ Technology Stack
*   **Backend**: PHP 8.x (Native)
*   **Database**: SQLite (Lightweight, no MySQL setup required)
*   **Frontend**: HTML5, Vanilla JS, Tailwind CSS
*   **API Integration**: Cobalt Tools API
*   **Admin Panel**: AdminLTE 4 (Bootstrap 5)

---

## 🚀 Deployment / Installation
We have prepared detailed, step-by-step guides for deploying this project to a VPS.

### 📄 Documentation (Panduan Lengkap)
Please refer to the `docs/` folder for comprehensive installation instructions in Indonesian:

1.  **[Backend Setup: Cobalt API](docs/PANDUAN_COBALT.md)**  
    *Panduan cara install Cobalt API menggunakan Docker di VPS.*
    
2.  **[Frontend Setup: MySeoFan Web App](docs/PANDUAN_APP.md)**  
    *Panduan cara deploy aplikasi web PHP ke Apache/Nginx dan konfigurasi HTTPS.*

### Quick Start (Local Development)
1.  Clone the repository.
    ```bash
    git clone https://github.com/yourusername/myseofan.git
    ```
2.  Install PHP dependencies.
    ```bash
    composer install
    ```
3.  Ensure `database/` directory is writable.
4.  Serve the application (using Laragon, XAMPP, or PHP built-in server).
    ```bash
    php -S localhost:8000
    ```

## 🔒 Security
*   **Sensitive Directories**: The project includes an `.htaccess` file configured to block access to `database/`, `includes/`, and `scripts/` in production.
*   **Input Validation**: All user inputs are sanitized before processing.

## 📄 License
This project is for personal and educational use. Please check the `LICENSE` file for more details.
