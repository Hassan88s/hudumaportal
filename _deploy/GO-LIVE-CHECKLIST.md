# Go live — the short checklist

Tick these in order. Do not skip ahead; each one assumes the last is done.

The long version with the reasoning behind each step is in
`PRODUCTION_RUNBOOK.md`. This page is just the doing.

---

## Part 1 — set up the connection (once, ~10 minutes)

- [ ] **1.** cPanel (production) → **FTP Accounts** → Create an account.
      Set its Directory to the folder that holds the live `index.php` —
      on this server that is `/home/hudumaportalco/public_html`.
      Write down the username, password and server address.

- [ ] **2.** GitHub → your repo → **Settings** → Secrets and variables →
      **Actions** → New repository secret. Add three, named exactly:

      PROD_FTP_HOST       = the server address from step 1
      PROD_FTP_USERNAME   = the username from step 1
      PROD_FTP_PASSWORD   = the password from step 1

      A typo in a name makes GitHub pass an empty string, and the run fails
      after about ten seconds.

- [ ] **3.** Prove the account is aimed at production before anything is
      uploaded. GitHub → **Actions** → **Deploy to Production** →
      **Run workflow** → tick **dry run** → Run.

      Read the file count in the log:

      - ~2,800 local files, 0 server files → correct.
      - a handful, or none → wrong. It found dev's sync state, so the FTP
        account is jailed to dev's folder. Fix the Directory and repeat.

      A dry run uploads nothing either way.

---

## Part 2 — back up production (~5 minutes)

Even with nothing in it. This is the only way back.

- [ ] **4.** cPanel → **File Manager** → select the whole live folder →
      Compress → `.zip` → Download it.

- [ ] **5.** cPanel → **phpMyAdmin** → pick the production database →
      **Export** → Go → save the file.

---

## Part 3 — the database (~15 minutes)

- [ ] **6.** phpMyAdmin on **dev** → pick the dev database → **Export** →
      Quick → SQL → Go. You now have a `.sql` file. It is only about 1 MB,
      so the normal Import tab will take it.

- [ ] **7.** phpMyAdmin on **production** → pick the production database →
      **Operations** tab → **Drop the database**. Then recreate it with the
      same name in cPanel → MySQL Databases, and re-add the same user to it
      with All Privileges.

      Why drop it: importing on top of old tables leaves whatever the dump
      does not mention, and you end up with half of one schema and half of
      another.

- [ ] **8.** Still in phpMyAdmin on production → the empty database →
      **Import** → choose the file from step 6 → Go.

- [ ] **9.** phpMyAdmin on production → **SQL** tab. Open
      `_deploy/prod_import_scrub.sql`, copy **only section 1**, run it.
      Read the list it prints. Every name should be either a test account or
      somebody you recognise. If something looks wrong, stop and ask.

- [ ] **10.** Same SQL tab: run the **whole** scrub file.
      Read the four lists it prints at the end. In particular:

      - the outgoing mail address is the one production should send from
      - every remaining admin is a person who should be able to sign in to
        the live panel — remove any contractor or developer who should not

---

## Part 4 — seed the server (~30 minutes)

Git will never send six things production needs. Five of them are in
`.gitignore`, so git has never held a copy at all:

    index.php                                   .gitignore
    .htaccess                                   .gitignore
    @core/.env                                  .gitignore
    @core/app/Providers/AppServiceProvider.php  .gitignore
    @core/vendor/                               .gitignore — Laravel itself
    @core/storage/framework/*                   excluded from the deploy

On dev these were already on the server before the first deploy, which is
why nobody noticed. An empty production folder given only a deploy cannot
boot: no entry point, no framework, no database credentials.

So seed it once by hand, then let the deploy take over.

- [ ] **11.** cPanel File Manager on **dev** → select the *entire* live
      folder → Compress → `.zip`. Everything: code, `vendor`, `@core/.env`,
      `index.php`, `.htaccess`, and `assets/uploads`.

      `assets/uploads` is around 3,400 files and 300 MB. It is user content,
      not code, and neither deploy ever touches it. Zip it — do not drag it
      over FTP file by file.

- [ ] **12.** Move the zip to production (download then upload, or copy it
      across if both sites are on the same server).

- [ ] **13.** cPanel File Manager on production → extract the zip into the
      live folder.

- [ ] **14.** If cPanel gives you **Terminal**, this is cleaner and much
      faster than shipping `vendor` inside the zip:

          cd ~/public_html/@core && composer install --no-dev -o

---

## Part 5 — the four files that must differ (~10 minutes)

These are the files git does not carry, so nothing will ever correct them
for you. They arrived from dev holding dev's values.

- [ ] **15.** Edit `@core/.env` on production:

          APP_URL=https://hudumaportal.co.tz
          APP_DEBUG=false
          DB_DATABASE=<production's database name>
          DB_USERNAME=<production's database user>
          DB_PASSWORD=<production's database password>

      `APP_DEBUG` is not cosmetic. Left at `true`, any error page prints a
      stack trace containing the database password to whoever triggered it.

- [ ] **16.** `index.php` — the security includes near the top must be
      active, not commented out.

- [ ] **17.** `.htaccess` — the www redirect and the session path must be the
      production ones.

- [ ] **18.** `@core/app/Providers/AppServiceProvider.php` —
      `URL::forceScheme('https')` must be uncommented.

---

## Part 6 — the code (~30 minutes the first time, seconds after)

- [ ] **19.** If the site is already serving visitors: admin panel →
      General Settings → turn **maintenance mode on**.
      If nobody is using the site yet, skip this.

- [ ] **20.** The `production` branch already exists and already tracks
      `main`. On your own machine, in `E:\Hudumaportal`:

          git checkout production
          git merge --ff-only main
          git push origin production
          git checkout main

      If that says "Everything up-to-date", the branches already match and
      no deploy will start. Use Actions → Deploy to Production →
      **Run workflow** with dry run **un-ticked** instead.

- [ ] **21.** GitHub → **Actions**. Watch "Deploy to Production" run.
      The first publish uploads all ~2,800 files and takes around 30
      minutes. Every run after that sends only what changed, in seconds.
      Wait for the green tick.

---

## Part 7 — finish (~15 minutes)

- [ ] **22.** cPanel → **Terminal**:

          cd ~/public_html/@core && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear

      Do this *after* the deploy goes green, not during. Skipping it is the
      most common reason a correct deploy looks broken.

- [ ] **23.** Confirm the deploy landed in the document root:

          ls -la ~/public_html/.ftp-deploy-sync-state.json

      GitHub writes that file itself. If it is beside the live `index.php`,
      the connection is proven. If it is somewhere else, that is where your
      FTP account is actually rooted.

- [ ] **24.** Open `https://hudumaportal.co.tz/system-upgrade` once.
      Safe to open again if you are unsure.

- [ ] **25.** Admin panel → **Rides → Ride Settings**. Confirm both switches
      are **off**. The fares there are placeholders, not real prices.

- [ ] **26.** Click through, logged out and logged in:
      home, a category, a service, search, register, log in, log out,
      buyer dashboard, seller dashboard, a test order to the payment screen,
      admin orders / services / users / settings.

- [ ] **27.** Turn maintenance mode off, if you turned it on at step 19.

- [ ] **28.** Remove the deploy marker — it has served its purpose and there
      is no reason to leave it on a public site:

          git rm deploy-check.txt
          git commit -m "Remove the deploy marker"
          git push origin main

      then promote it the usual way.

---

## If it goes wrong

Restore the step 4 zip over the live folder, and import the step 5 database
export. Both are from the same moment, so they match each other.

---

## Afterwards, every change is this

    git checkout production && git merge --ff-only main && git push && git checkout main

Only changed files upload — seconds, not minutes. No maintenance mode.
Clear caches afterwards if the change touched config, routes or views.
