# Infrastructure Comparison: Before vs After

## Quick Answer

**Yes, you need some infrastructure changes, but the new setup is actually SIMPLER.**

---

## Side-by-Side Comparison

| Component | Before (PHP) | After (Python) | Change |
|-----------|-------------|----------------|---------|
| **Runtime** | PHP 8.4 + PHP-FPM | Python 3.9+ | Replace |
| **Web Server** | Nginx/Apache | Nginx/Apache (reconfigured) | Reconfigure |
| **Search** | Apache Solr (separate service) | PostgreSQL (built-in) | **Remove Solr!** |
| **Database** | File-based storage | PostgreSQL | Add |
| **Process Manager** | PHP-FPM | systemd/supervisor | Different |
| **Dependencies** | Composer packages | pip packages | Replace |
| **Services Running** | 3 (Web + PHP + Solr) | 2 (Web + Python) | **-1 service** |

---

## What You Need to Install

### ✅ Add These
```bash
# Python runtime
sudo apt install python3 python3-pip python3-venv

# PostgreSQL database
sudo apt install postgresql

# Process manager (if not already present)
sudo apt install supervisor  # or use systemd
```

### ❌ Can Remove These (After Migration)
```bash
# PHP and extensions
sudo apt remove php php-fpm php-xml php-curl php-mbstring

# Apache Solr
# Stop Solr service and remove

# Composer
sudo rm /usr/local/bin/composer
```

---

## Server Configuration Changes

### Web Server (Nginx Example)

**Before:** Proxy to PHP-FPM
```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

**After:** Proxy to Python app
```nginx
location / {
    proxy_pass http://127.0.0.1:8000;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

**That's it!** Just change how you proxy requests.

---

## Server Requirements

### Minimum Server Specs

| Requirement | Before (PHP) | After (Python) |
|-------------|--------------|----------------|
| **CPU** | 1 core | 1 core |
| **RAM** | 1.5-2 GB | 1 GB |
| **Disk** | 500 MB | 500 MB |
| **Services** | Web + PHP-FPM + Solr | Web + Python + PostgreSQL |

**You can actually use a SMALLER server** (no Solr = less RAM needed)

### Recommended Production

| Requirement | Recommendation |
|-------------|----------------|
| **CPU** | 2 cores |
| **RAM** | 2-4 GB |
| **Disk** | 5 GB (includes logs, backups) |
| **Bandwidth** | Same as before |

---

## Port Changes

| Service | Port | Notes |
|---------|------|-------|
| Web Server (80/443) | No change | Same as before |
| Python App | 8000 (internal) | Only accessible via localhost |
| PostgreSQL | 5432 (internal) | Only accessible via localhost |
| ~~Solr~~ | ~~8983~~ | **Can remove!** |

**Firewall:** Only ports 80 and 443 need to be open (same as before)

---

## Services Architecture

### Before
```
Internet
  ↓
Nginx/Apache (Port 80/443)
  ↓
PHP-FPM (Unix socket) → Solr (Port 8983)
  ↓
File System
```

**3 services to manage**

### After
```
Internet
  ↓
Nginx/Apache (Port 80/443)
  ↓
FastAPI/Uvicorn (Port 8000) → PostgreSQL (Port 5432)
  ↓
File System
```

**2 main services to manage** (simpler!)

---

## Deployment Steps Summary

### 1. Install Software (5 minutes)
```bash
sudo apt install python3 python3-pip postgresql
```

### 2. Setup Database (2 minutes)
```bash
sudo -u postgres createdb blake_quarterly
sudo -u postgres createuser blake -P
```

### 3. Deploy App (5 minutes)
```bash
cd /var/www/quarterly
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
python -m app.indexer  # Index documents
```

### 4. Configure Web Server (5 minutes)
```bash
# Edit nginx config to proxy to port 8000
sudo nano /etc/nginx/sites-available/blake-quarterly
sudo systemctl reload nginx
```

### 5. Setup Process Manager (3 minutes)
```bash
# Create systemd service
sudo nano /etc/systemd/system/blake-quarterly.service
sudo systemctl enable blake-quarterly
sudo systemctl start blake-quarterly
```

**Total:** ~20 minutes for initial setup

---

## What Stays The Same

✅ Your domain name and DNS
✅ Your SSL certificates
✅ Your file structure (docs/, html/, xsl/, etc.)
✅ Your content (all XML/HTML documents)
✅ Your URLs (same paths)
✅ Your web server (just reconfigured)

---

## Migration Difficulty

| Task | Difficulty | Time |
|------|-----------|------|
| Install Python | ⭐ Easy | 5 min |
| Install PostgreSQL | ⭐ Easy | 5 min |
| Deploy application | ⭐⭐ Medium | 10 min |
| Index documents | ⭐ Easy | 5 min |
| Configure web server | ⭐⭐ Medium | 10 min |
| Setup systemd service | ⭐⭐ Medium | 5 min |
| Test and verify | ⭐⭐ Medium | 15 min |
| **TOTAL** | **⭐⭐ Medium** | **~1 hour** |

---

## Cost Impact

### Cloud Hosting (e.g., DigitalOcean, Linode, AWS)

**Before:**
- Need ~2 GB RAM for PHP + Solr
- Example: $12-18/month

**After:**
- Need ~1-2 GB RAM for Python + PostgreSQL
- Example: $6-12/month

**Potential savings:** ~$72/year 💰

### Maintenance Time

**Before:**
- Manage 3 services (Web, PHP, Solr)
- Update PHP regularly
- Maintain Solr index
- Monitor Solr JVM memory

**After:**
- Manage 2 services (Web, Python)
- Update Python (less frequent)
- PostgreSQL auto-manages indexes
- Simpler monitoring

**Time savings:** ~2-4 hours/month 🕐

---

## Risk Assessment

### Low Risk ✅
- Python is stable and widely used
- PostgreSQL is battle-tested
- FastAPI is production-ready
- No vendor lock-in

### Medium Risk ⚠️
- Need to learn Python deployment (this guide helps)
- Brief downtime during migration
- Need to test thoroughly

### Mitigation
- Test on staging server first
- Keep old PHP site as backup
- Use blue-green deployment
- Plan migration during low-traffic period

---

## Do You Need a New Server?

### Option 1: Same Server (Recommended)
**Pros:**
- No DNS changes
- No SSL certificate transfer
- Simpler migration
- Can run both side-by-side temporarily

**Cons:**
- Brief downtime during switch
- Need to uninstall PHP/Solr after

### Option 2: New Server
**Pros:**
- Zero downtime (just switch DNS)
- Old server stays as backup
- Cleaner installation

**Cons:**
- Need to transfer files
- SSL certificates to transfer
- DNS propagation time
- Extra cost temporarily

**Recommendation:** Use same server if possible.

---

## Common Questions

### Q: Can I run PHP and Python side-by-side during migration?
**A:** Yes! Use different ports:
- PHP site on port 8080
- Python site on port 8000
- Test Python thoroughly before switching

### Q: What if something goes wrong?
**A:** Keep PHP site ready:
- Don't delete PHP code immediately
- Keep database dump
- Easy to switch back in Nginx config

### Q: Do I need to learn Python?
**A:** Not for basic operations:
- Adding documents: `python add_document.py file.xml`
- Restarting: `sudo systemctl restart blake-quarterly`
- Logs: `sudo journalctl -u blake-quarterly`

For advanced changes, yes, but it's simpler than PHP.

### Q: Is PostgreSQL harder to manage than Solr?
**A:** No, it's MUCH easier:
- Solr requires Java, complex configuration, manual indexing
- PostgreSQL is standard, automatic backups, built-in full-text search
- PostgreSQL has better tools and documentation

### Q: What about performance?
**A:** Should be similar or better:
- FastAPI is async and fast
- PostgreSQL full-text search is efficient
- Fewer services = less overhead
- Static files served by Nginx (no change)

---

## Bottom Line

### Infrastructure Changes Needed: Yes, but...

✅ **Simpler overall** (2 services instead of 3)
✅ **Cheaper to run** (less RAM needed)
✅ **Easier to maintain** (fewer moving parts)
✅ **More reliable** (modern stack)
✅ **Better documented** (popular stack)

### Timeline

- **Testing:** 1-2 hours
- **Staging deployment:** 2-3 hours
- **Production migration:** 1 hour (plus testing)
- **Total effort:** ~4-6 hours

### Recommendation

**Do it!** The infrastructure changes are straightforward, well-documented, and result in a simpler, more maintainable system.

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for detailed step-by-step instructions.
