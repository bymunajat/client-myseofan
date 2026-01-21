# Project Status & Context

**Last Updated:** 21 Januari 2026, 17:10 WIB  
**Project:** MySeoFan - Instagram Downloader  
**Client:** bymunajat  
**Branch:** dev

---

## 📊 Current Project State

### ✅ Completed Features
- Multi-language system (EN, ID, ES, FR, DE, JA)
- Auto-translation from English to all languages
- Clean URLs (no .php extensions)
- Menu management (English-only, auto-translate)
- Page management (English-only, auto-translate)
- Blog system with categories
- Instagram downloader (Video, Photo, Reels, IGTV, Carousel, Story)

### 🔧 Recent Changes (21 Jan 2026)
1. **URL Path Fix** - Resolved Windows path issues (`C:/laragon/...`)
2. **Redirect Loop Fix** - Fixed `.htaccess` rules
3. **Multi-language Simplification** - Single source (English) + auto-translate

### ⚠️ Known Issues
1. **Performance** - Loading lama karena translate on-the-fly (belum ada cache)
2. **SEO** - Missing hreflang, meta tags per language, schema markup
3. **Mobile** - Icon terlalu besar, tidak responsive
4. **Language Management** - Belum ada admin UI untuk add/delete language

---

## 🎯 Priority Tasks (Next Session)

### HIGH PRIORITY (Must Do First)
1. **SEO Settings** 🔴 CRITICAL
   - Add hreflang tags
   - Meta tags per language (title, description, OG)
   - Schema markup per language
   - Add noindex/nofollow to all pages

2. **Performance Optimization** 🔴 CRITICAL
   - Implement translation caching
   - Pre-translate saat admin save
   - Setup LibreTranslate (pending RAM check)

### MEDIUM PRIORITY
3. **Mobile Responsive**
   - Fix icon sizes
   - Downloader UI (icon bukan dropdown)

4. **Language Management Admin**
   - CRUD interface untuk manage languages
   - Dynamic language loading from database

### LOW PRIORITY
5. **Custom URL redirects** (fr → fr1)

---

## 📁 Important Files & Locations

### Core Files
- `includes/db.php` - Database functions, getUrl(), getMenuTree()
- `includes/Translator.php` - Translation logic (Google Translate API)
- `.htaccess` - URL rewriting, clean URLs
- `index.php` - Homepage
- `blog.php` - Blog listing
- `post.php` - Single post view

### Admin Files
- `admin/pages.php` - Page management (English-only)
- `admin/menus.php` - Menu management (English-only)
- `admin/posts.php` - Blog post management

### Documentation
- `docs/daily-log-2026-01-21.md` - Today's work log
- `README.md` - Project overview

---

## 🔑 Key Technical Decisions

### Multi-Language Strategy
- **Single Source of Truth:** All content created in English
- **Auto-Translation:** On-the-fly translation via Google Translate API
- **No Duplication:** No separate entries per language in database

### URL Structure
- English: `/photo-downloader/`
- Other languages: `/id/photo-downloader/`, `/es/photo-downloader/`
- Clean URLs (no .php extensions)

### Translation Flow
1. Admin creates content in English
2. Content saved to database (lang_code = 'en')
3. User visits `/id/blog/`
4. System fetches English content
5. Translator::translate() called for each text
6. Translated content displayed

**Problem:** No caching → slow performance

---

## 💾 Database Schema (Key Tables)

### pages
- id, title, slug, content, lang_code (always 'en'), meta_title, meta_description

### posts
- id, title, slug, content, lang_code (always 'en'), category_id, featured_image

### menu_items
- id, menu_location, lang_code (always 'en'), type, label, url, parent_id, sort_order

### categories
- id, name, slug, lang_code (always 'en')

---

## 🚀 Git Workflow

### Branch Structure
- `main` - Production (stable)
- `dev` - Development (active work)
- `staging` - Development (testing)


### Commit Convention
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation
- `refactor:` - Code refactoring
- `perf:` - Performance improvements

### Recent Commits (dev branch)
1. `1b13bb7` - feat: simplify multi-language content management
2. `9f0b11a` - fix: resolve URL path issues, redirect loops, and menu management

---

## 🔄 Pending Client Decisions

1. **RAM Server Check** - Untuk LibreTranslate deployment
2. **Translation Strategy** - LibreTranslate vs Google Translate + Cache
3. **Language Management** - Approval untuk dynamic language admin feature

---

## 📝 Notes for Next AI Session

### Context to Remember
- Client prefer **simplicity** over complexity
- Budget-conscious (prefer **free solutions**)
- Want **self-service features** (manage via admin, no code editing)
- **SEO is critical** - must follow fastdl.app as reference
- **Performance is critical** - loading must be fast

### What to Ask Client
1. Hasil cek RAM server (untuk LibreTranslate)
2. Priority: SEO dulu atau Performance dulu?
3. Approval untuk language management feature

### Quick Start Commands
```bash
# Start development
cd c:\laragon\www\client-myseofan
git checkout dev
git pull origin dev

# Check status
git status

# Commit changes
git add .
git commit -m "feat: description"
git push origin dev
```

---

**End of Context Document**  
**Next Session:** Continue with SEO settings or Performance optimization (based on client priority)
