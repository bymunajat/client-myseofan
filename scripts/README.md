# Scripts

This folder contains utility scripts for the MySeoFan project.

## Available Scripts

### 📝 Documentation Scripts

#### `generate-daily-log.php`
**Purpose:** Auto-generate daily work log template  
**Usage:**
```bash
php scripts/generate-daily-log.php
```

**Output:** Creates `docs/daily-log-YYYY-MM-DD.md` with pre-filled template

---

### 🗄️ Database Scripts

#### `cleanup_menus.php`
**Purpose:** Clean up non-English menu items  
**Usage:**
```bash
php cleanup_menus.php
```

#### `add_blog_menus.php`
**Purpose:** Add blog menu items for all languages  
**Usage:**
```bash
php add_blog_menus.php
```

#### `inspect_db.php`
**Purpose:** Inspect database content (pages, menu items)  
**Usage:**
```bash
php inspect_db.php
```

#### `debug_menu.php`
**Purpose:** Debug menu generation and URL paths  
**Usage:**
```bash
php debug_menu.php
```

---

## 🚀 Quick Start

```bash
# Navigate to project root
cd c:\laragon\www\client-myseofan

# Run any script
php scripts/generate-daily-log.php
```

---

## 📋 Notes

- All scripts should be run from project root
- Database scripts require `includes/db.php`
- Check script output for success/error messages
