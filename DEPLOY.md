# Deployment Guide

## Production Server

- **Host:** `ssh.spq.app` (port 18765)
- **User:** `u685-ubgnyznjtpvj`
- **Key:** `~/.ssh/id_ed25519`
- **Path:** `~/www/spq.app/public_html/`
- **DB:** `dbbcno9xx4absw` (user: `udtguuukdzw4j`)

## Deploy Steps

### 1. Deploy PHP files (controllers, models, views, routes, lang)

```bash
# Single file
scp -P 18765 -i ~/.ssh/id_ed25519 \
  local/path/file.php \
  u685-ubgnyznjtpvj@ssh.spq.app:~/www/spq.app/public_html/remote/path/file.php

# Directory
scp -P 18765 -i ~/.ssh/id_ed25519 -r \
  local/dir/ \
  u685-ubgnyznjtpvj@ssh.spq.app:~/www/spq.app/public_html/remote/dir/
```

### 2. Clear Laravel caches

```bash
ssh -p 18765 -i ~/.ssh/id_ed25519 u685-ubgnyznjtpvj@ssh.spq.app \
  "cd ~/www/spq.app/public_html && php artisan view:clear && php artisan route:clear && php artisan config:clear"
```

### 3. Database changes (no migrations system — direct ALTER TABLE)

```bash
ssh -p 18765 -i ~/.ssh/id_ed25519 u685-ubgnyznjtpvj@ssh.spq.app \
  "mysql -u udtguuukdzw4j -p'<password>' dbbcno9xx4absw -e 'ALTER TABLE ...'"
```

### 4. Deploy daemon (on Mac Mini)

Since the CLI session runs on the Mac Mini:

```bash
# The daemon auto-updates via DAEMON_VERSION check
# To force restart:
pkill -f spq_daemon.py
nohup python3 -u /path/to/spq_daemon.py --token <TOKEN> --poll-interval 5 &

# Or via admin UI: Mac Machine → Restart Daemon
# Or via LaunchAgent:
launchctl unload ~/Library/LaunchAgents/com.spq.daemon.plist
launchctl load ~/Library/LaunchAgents/com.spq.daemon.plist
```

### 5. Verify

```bash
curl -s -o /dev/null -w "%{http_code}" https://spq.app/admin/dashboard
# Should return 302 (redirect to login) — confirms routes work
```

## Notes

- **No CI/CD** — deployment is manual via SCP
- **No migration system** — DB changes are direct ALTER TABLE statements
- **Init files** — `app/database/migrations/0001_*.php` are reference init scripts, not run via `artisan migrate`
- **Daemon versioning** — increment `DAEMON_VERSION` in `daemon/spq_daemon.py`, the daemon will self-update within 5 minutes