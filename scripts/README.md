# Scripts Directory

This directory contains utility and diagnostic scripts for the MySeoFan project.

## Diagnostic Scripts

### `check_db.php`
**Purpose:** Database structure inspector  
**Usage:** `php check_db.php`  
**Description:** Displays all tables and their column information with detailed metadata including primary keys, NOT NULL constraints, and default values.

### `debug_menu.php`
**Purpose:** Menu items debug utility  
**Usage:** `php debug_menu.php`  
**Description:** Shows comprehensive menu structure statistics including items per language, items per location, and detailed menu hierarchy.

### `diagnostic_list_pages.php`
**Purpose:** Pages diagnostic utility  
**Usage:** `php diagnostic_list_pages.php`  
**Description:** Lists all pages with their metadata in JSON format. Useful for API testing and data verification.

## Migration Scripts

### `migrate_blog_features.php`
Database migration script for blog features.

### `migrate_menus.php`
Database migration script for menu system.

## Seeding Scripts

### `seed_footer_sections.php`
Seeds footer menu sections.

### `seed_full_pages.php`
Seeds complete page content for all languages.

### `seed_menus.php`
Seeds menu items for header and footer navigation.

### `seed_nav_defaults.php`
Seeds default navigation items.

### `seed_translations_all.php`
Seeds translation strings for all supported languages.

## Setup Scripts

### `setup_translation_cache.php`
Sets up translation caching system.

### `setup_translations.php`
Initial translation system setup.

---

## Notes

- All scripts should be run from the project root directory
- Database scripts require proper database configuration in `config.php`
- Seeding scripts are idempotent and can be run multiple times safely
- Migration scripts should only be run once during deployment

## Best Practices

1. **Always backup** the database before running migration scripts
2. **Test in development** environment first
3. **Review output** of diagnostic scripts regularly
4. **Keep scripts updated** as the database schema evolves
