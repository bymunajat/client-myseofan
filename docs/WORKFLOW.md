# Documentation Workflow

## 📚 Overview
This folder contains project documentation and daily work logs.

---

## 📁 File Structure

```
docs/
├── README.md                    # This file
├── PROJECT_STATUS.md            # Current project status (living document)
├── WORKFLOW.md                  # Documentation workflow guide
├── daily-log-YYYY-MM-DD.md     # Daily work logs
└── archive/                     # Archived logs (optional)
    └── YYYY-MM/
        └── daily-log-*.md
```

---

## 🔄 Daily Workflow

### **Morning (Start of Work):**
1. ✅ Read `PROJECT_STATUS.md` (2-3 minutes)
2. ✅ Read yesterday's `daily-log-[date].md` (if needed)
3. ✅ Generate today's log: `php scripts/generate-daily-log.php`
4. ✅ Start working

### **During Work:**
- Take notes of important decisions
- Track time spent on each task
- Note any issues/blockers

### **Evening (End of Work):**
1. ✅ Fill in today's `daily-log-[date].md`
2. ✅ Update `PROJECT_STATUS.md`:
   - Update "Recent Changes" (max 5 items)
   - Update "Priority Tasks"
   - Update "Pending Client Decisions"
3. ✅ Commit & push to git

---

## 📝 File Descriptions

### **PROJECT_STATUS.md** (Living Document)
**Purpose:** Quick context for AI/developers  
**Update:** Every day (end of work)  
**Keep:** Current info only (max 200 lines)  
**Remove:** Old/irrelevant items

**Sections:**
- Current Project State
- Recent Changes (max 5)
- Known Issues
- Priority Tasks
- Important Files
- Technical Decisions
- Pending Client Decisions
- Notes for Next AI Session

### **daily-log-YYYY-MM-DD.md** (Permanent Archive)
**Purpose:** Detailed work log  
**Create:** Daily (use script)  
**Keep:** Forever (permanent record)  
**Archive:** Monthly (optional)

**Sections:**
- Completed Tasks
- Issues & Discussions
- Next Steps
- Git Summary
- Notes & Learnings
- Time Tracking
- Pending Actions

---

## 🛠️ Scripts

### Generate Daily Log
```bash
# From project root
php scripts/generate-daily-log.php
```

This creates a new daily log with pre-filled template.

---

## 📦 Archive Strategy (Optional)

When you have >30 daily logs, archive old ones:

```bash
# Create archive folder
mkdir -p docs/archive/2026-01

# Move old logs
mv docs/daily-log-2026-01-*.md docs/archive/2026-01/
```

**Keep in main `docs/`:**
- Current week's logs
- PROJECT_STATUS.md
- README.md
- WORKFLOW.md

---

## ✅ Best Practices

### **Keep It Simple:**
- ✅ Daily log = 15-20 minutes to write
- ✅ PROJECT_STATUS = Update only what changed
- ✅ Use templates (auto-generate)

### **Be Consistent:**
- ✅ Update docs **every day**
- ✅ Don't skip days (hard to catch up)
- ✅ Review PROJECT_STATUS every Friday

### **Stay Organized:**
- ✅ Archive old logs monthly
- ✅ Keep PROJECT_STATUS short (<200 lines)
- ✅ Use clear, descriptive task names

---

## 🎯 Benefits

| Benefit | Impact |
|---------|--------|
| **AI Context** | 30-60 min saved/day |
| **Project Continuity** | Easy handoff to new developers |
| **Accountability** | Clear progress tracking |
| **Knowledge Base** | Permanent technical documentation |

**Total Time Saved:** ~3-4 hours/week ✅

---

## 📞 Quick Reference

### Daily Checklist
- [ ] Morning: Read PROJECT_STATUS.md
- [ ] Morning: Generate daily log
- [ ] During: Take notes
- [ ] Evening: Fill daily log
- [ ] Evening: Update PROJECT_STATUS.md
- [ ] Evening: Commit & push

### File Naming
- Daily logs: `daily-log-YYYY-MM-DD.md`
- Archive: `archive/YYYY-MM/`

### Git Commits
```bash
git add docs/
git commit -m "docs: daily log for YYYY-MM-DD"
git push origin dev
```

---

**Questions?** Check `PROJECT_STATUS.md` for current context.
