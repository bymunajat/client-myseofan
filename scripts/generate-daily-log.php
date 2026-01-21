<?php
/**
 * Daily Log Generator
 * 
 * Usage: php scripts/generate-daily-log.php
 * 
 * This script creates a new daily log file with pre-filled template
 */

$date = date('Y-m-d');
$dateFormatted = date('l, d F Y'); // e.g., "Tuesday, 21 January 2026"
$filename = __DIR__ . "/../docs/daily-log-{$date}.md";

// Check if file already exists
if (file_exists($filename)) {
    echo "❌ Daily log for {$date} already exists!\n";
    echo "File: {$filename}\n";
    exit(1);
}

// Template
$template = <<<MARKDOWN
# Daily Work Log - {$dateFormatted}

## 📅 Tanggal: {$dateFormatted}
**Developer:** Antigravity AI  
**Project:** MySeoFan - Instagram Downloader  
**Client:** bymunajat

---

## ✅ Pekerjaan yang Diselesaikan Hari Ini

### 1. **[Task Name]** ⏰ HH:MM - HH:MM
**Problem:**
- [Describe the problem]

**Solution:**
- ✅ [What was done]
- ✅ [What was done]

**Files Modified:**
- `path/to/file.php` (description)

**Git Commit:** `[hash]` - "[commit message]"

---

## 🔍 Issues Ditemukan & Diskusi

### 1. **[Issue Name]** ⏰ HH:MM
**Problem:**
- [Describe the issue]

**Root Cause:**
- [Why it happened]

**Proposed Solution:**
- [How to fix]

**Status:** ⏸️ **PENDING** / ✅ **RESOLVED**

---

## 📋 Next Steps (To-Do)

### High Priority:
1. ⏸️ **[Task]** - [Description]

### Medium Priority:
2. ⏸️ **[Task]** - [Description]

### Low Priority:
3. ⏸️ **[Task]** - [Description]

---

## 🚀 Git Summary

### Commits Today:
1. **[hash]** - "[commit message]"
   - X files changed, Y insertions(+), Z deletions(-)

### Branch: `dev`
### Total Changes: X files modified

---

## 📝 Notes & Learnings

### Technical Insights:
1. **[Topic]:** [What was learned]

### Client Preferences:
- [Any new preferences discovered]

---

## ⏰ Time Tracking

| Task | Duration | Status |
|------|----------|--------|
| [Task name] | ~X jam | ✅ Done / ⏸️ Pending |

**Total Work Time:** ~X jam

---

## 🔄 Pending Client Actions

1. ✅ **[Action]** - [Description]

---

**End of Daily Log**  
**Next Session:** [What to focus on next]

MARKDOWN;

// Create file
file_put_contents($filename, $template);

echo "✅ Daily log created successfully!\n";
echo "File: {$filename}\n";
echo "\nNext steps:\n";
echo "1. Fill in the template with today's work\n";
echo "2. Update PROJECT_STATUS.md\n";
echo "3. Commit changes to git\n";
