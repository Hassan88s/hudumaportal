# Moving dev to production

Two different jobs, kept apart on purpose:

- **The cutover** — the one time production catches up with everything dev has. Done once.
- **The routine** — every small change after that. Done in two minutes, repeatedly.

---

## How the branches work

| Branch | Deploys to | When |
|---|---|---|
| `main` | dev.hudumaportal.co.tz | automatically, on every push |
| `production` | hudumaportal.co.tz | automatically, when `production` is pushed |

Dev keeps moving. Production only changes when somebody promotes a commit that
dev has already proved. Promoting is one command:

```
git checkout production && git merge --ff-only main && git push && git checkout main
```

`--ff-only` matters: it refuses if production has drifted, instead of silently
making a merge commit.

---

## What git never touches on the server

These exist on the server and differ per environment. They are excluded from
both deploys, so the server's copies are never overwritten:

- `@core/.env` — DB credentials, APP_URL, gateway keys
- `index.php` — production has the security includes active
- `.htaccess` — production has the www redirect and session path
- `@core/app/Providers/AppServiceProvider.php` — production forces HTTPS
- `.user.ini`
- `assets/uploads/` and `@core/public/assets/uploads/` — user uploads
- `@core/vendor/` — installed once by composer on the server
- `@core/storage/` caches, logs, sessions
- `security/` — separate panel with its own database

So production keeps its own config forever. Nothing in a deploy can break it.

---

## Part 1 — the cutover

### Before you start

1. **Composer is not a problem.** `composer.json` and `composer.lock` have not
   changed since the baseline, so production's existing `@core/vendor/` is
   correct. No composer run needed.

2. **Create a dedicated FTP account for production** in cPanel, with its
   directory set to the live docroot (the folder holding `index.php`). The
   deploy uses `server-dir: /`, which is only correct if the FTP user is jailed
   to that folder — the same arrangement dev already uses.

3. **Add four repository secrets** in GitHub (Settings -> Secrets and variables
   -> Actions):
   - `PROD_FTP_HOST`
   - `PROD_FTP_USERNAME`
   - `PROD_FTP_PASSWORD`

   Until these exist the production workflow cannot run, so the file is inert.

### Step A — back up production, completely

Both of these, before anything else:

- **Files**: in cPanel File Manager, compress the whole docroot to a `.zip` and
  download it. This is the only way back if the upload goes wrong.
- **Database**: phpMyAdmin -> Export -> the full database, SQL format, downloaded.

Do not skip either. Everything below is reversible only because of this step.

### Step B — work out what the database is missing

Production's schema is behind dev's by several features. The file deploy does
not touch the database at all, so this has to be handled separately.

1. In phpMyAdmin on **production**, export the schema only (Export -> Custom ->
   Structure only) and save the file.
2. Hand that file over and the exact delta SQL gets written for it — only the
   tables and columns production actually lacks, in the right order.

Guessing here is what breaks a cutover, so the delta is built from production's
real schema rather than from the list of files in `_deploy/`.

### Step C — put production into maintenance mode

Admin panel -> General Settings -> there is a maintenance toggle. Turn it on.
The upload takes 20-40 minutes for ~2,400 files, and during that window the
site is running a mixture of old and new code. Visitors should not see that.

### Step D — run the delta SQL

phpMyAdmin -> SQL tab -> run the delta from Step B. Schema first, then any data
rows. If anything errors, stop and restore from the Step A backup.

### Step E — push the files

```
git checkout production 2>/dev/null || git checkout -b production
git merge --ff-only main
git push -u origin production
```

That first push uploads everything, because the server has no sync state yet —
which is exactly the full copy you want. Watch it in the repo's Actions tab.

Before the real run, you can preview: Actions -> Deploy to Production -> Run
workflow -> tick **dry run**. That lists every file it would send and uploads
nothing.

### Step F — clear the caches on production

cPanel Terminal, in the `@core` folder:

```
php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear
```

Skipping this is the single most common reason a correct deploy looks broken:
the old compiled config and views keep being served.

### Step G — run the upgrade URL

Open `https://hudumaportal.co.tz/system-upgrade` once. It applies the permission
and settings steps, records what it did, and is safe to open repeatedly — each
step runs at most once.

### Step H — check it, then open the doors

Click through, logged out and logged in:

- home, a category page, a service page, search
- register, log in, log out
- buyer dashboard; seller dashboard
- place a test order through to the payment screen
- admin: orders, services, users, general settings
- the referral page and a referral link

Then turn maintenance mode off.

### If it goes wrong

Restore the Step A file zip over the docroot and import the Step A database
dump. Both backups are from the same moment, so they are consistent with each
other.

---

## Part 2 — the routine, from then on

For every ordinary change:

1. Build and test locally.
2. Push to `main`. Dev updates on its own. Check it on dev.
3. Promote:

   ```
   git checkout production && git merge --ff-only main && git push && git checkout main
   ```

4. The Action uploads **only the changed files** — it keeps a sync state file on
   the server and compares against it, so a one-file fix uploads one file and
   takes seconds.
5. Clear caches on production if the change touched config, routes, views or
   translations:

   ```
   php artisan config:clear && php artisan view:clear && php artisan route:clear
   ```

6. If the change needed schema or settings, add it as a step in
   `@core/app/Services/SystemUpgradeService.php` and open
   `https://hudumaportal.co.tz/system-upgrade` after the deploy.

No maintenance mode needed for small changes — a handful of files lands in
seconds.

### Two rules worth keeping

**Database changes go in the upgrade service, not in a loose SQL file.** A step
there is idempotent and records that it ran, so the same URL works on dev and
production and cannot be applied twice. Loose SQL in `_deploy/` has to be
remembered, run by hand, and tracked in somebody's head.

**Never edit files directly on the production server.** The next deploy
overwrites them without warning, and the sync state will believe the server
matches git when it does not. If something must be changed on the server, change
it in git and promote.
