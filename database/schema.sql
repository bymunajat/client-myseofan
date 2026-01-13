-- Database Schema for Instagram Downloader (client-myseofan)

CREATE DATABASE IF NOT EXISTS myseofan_db;
USE myseofan_db;

-- 1. Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) DEFAULT 'Instagram Downloader',
    logo_path VARCHAR(255),
    favicon_path VARCHAR(255),
    header_code TEXT,
    footer_code TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. SEO Data Table
CREATE TABLE IF NOT EXISTS seo_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_identifier VARCHAR(100) UNIQUE, -- e.g., 'home', 'video', 'reels', 'blog_1'
    meta_title VARCHAR(255),
    meta_description TEXT,
    og_image VARCHAR(255),
    schema_markup TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Languages Table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang_code VARCHAR(10) UNIQUE, -- e.g., 'en', 'id', 'es'
    lang_name VARCHAR(50),
    is_default BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Blog Posts Table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    content LONGTEXT,
    thumbnail VARCHAR(255),
    seo_title VARCHAR(255),
    seo_desc TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 5. Admin Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Initial Data
INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- pw: password
INSERT INTO languages (lang_code, lang_name, is_default) VALUES ('en', 'English', TRUE), ('id', 'Indonesia', FALSE);
INSERT INTO site_settings (site_name) VALUES ('Instagram Downloader');
INSERT INTO seo_data (page_identifier, meta_title, meta_description) VALUES 
('home', 'Download Instagram Videos, Photos, Reels & Stories - MySeoFan', 'The best free online tool to download Instagram media instantly. Safe, fast, and high-quality.'),
('video', 'Instagram Video Downloader', 'Download Instagram videos in high quality MP4 format.'),
('reels', 'Instagram Reels Downloader', 'Save Instagram Reels easily with our fast downloader.'),
('image', 'Instagram Image Downloader', 'Download Instagram photos and carousels in original resolution.');
