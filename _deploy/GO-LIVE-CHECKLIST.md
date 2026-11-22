# Go live — the short checklist

Tick these in order. Do not skip ahead; each one assumes the last is done.

The long version with the reasoning behind each step is in
`PRODUCTION_RUNBOOK.md`. This page is just the doing.

---

## Part 1 — set up the connection (once, ~10 minutes)

- [ ] **1.** cPanel (production) → **FTP Accounts** → Create an account.
      Set its Directory to the folder that holds the live `index.php`.
      Write down the username, password and server address.

- [ ] **2.** GitHub → your repo → **Settings** → Secrets and variables →
      **Actions** → New repository secret. Add three, named exactly:

      PROD_FTP_HOST       = the server address from step 1
      PROD_FTP_USERNAME   = the username from step 1
      PROD_FTP_PASSWORD   = the password from step 1

---

## Part 2 — back up production (~5 minutes)

Even with nothing in it. This is the only way back.

- [ ] **3.** cPanel → **File Manager** → select the whole live folder →
      Compress → `.zip` → Download it.

- [ ] **4.** cPanel → **phpMyAdmin** → pick the production database →
      **Export** → Go → save the file.

---

## Part 3 — the database (~15 minutes)

- [ ] **5.** phpMyAdmin on **dev** → pick the dev database → **Export** →
      Quick → SQL → Go. You now have a `.sql` file.

- [ ] **6.** phpMyAdmin on **production** → pick the production database →
      **Operations** tab → **Drop the database**. Then recreate it with the
      same name, or ask cPanel → MySQL Databases to make it again.

      Why drop it: importing on top of old tables leaves whatever the dump
      does not mention, and you end up with half of one schema and half of
      another.

- [ ] **7.** Still in phpMyAdmin on production → the empty database →
      **Import** → choose the file from step 5 → Go.
      Large files may need cPanel's File Manager upload + the Import tab's
      file-on-server option instead.

- [ ] **8.** phpMyAdmin on production → **SQL** tab. Open
      `_deploy/prod_import_scrub.sql`, copy **only section 1**, run it.
      Read the list it prints. Every name should be either a test account
      or somebody you recognise. If something looks wrong, stop and ask.

- [ ] **9.** Same SQL tab: run the **whole** scrub file.
      Read the four lists it prints at the end. In particular check the
      outgoing mail address is the one production should send from.

- [ ] **10.** Leave `@core/.env` on the production server exactly as it is.
      Its database name, user and password are production's own.

---

## Part 4 — the uploaded files (~20 minutes)

Git never carries these. Skip this and the site is all broken images.

- [ ] **11.** cPanel File Manager on **dev** → select `assets/uploads` →
      Compress → `.zip`.

- [ ] **12.** Move that zip to production (download then upload, or copy it
      across if both are on the same server).

- [ ] **13.** cPanel File Manager on production → put the zip beside the live
      `index.php` → **Extract**.

- [ ] **14.** Do the same for `@core/public/assets/uploads` if it has anything
      in it.

---

## Part 5 — the code (~30 minutes, mostly waiting)

- [ ] **15.** If the site is already serving visitors: admin panel →
      General Settings → turn **maintenance mode on**.
      If nobody is using the site yet, skip this.

- [ ] **16.** On your own machine, in `E:\Hudumaportal`, run:

      git checkout -b production
      git merge --ff-only main
      git push -u origin production

- [ ] **17.** GitHub → your repo → **Actions** tab. Watch "Deploy to
      Production" run. First time uploads everything, 20–40 minutes.
      Wait for the green tick.

---

## Part 6 — finish (~10 minutes)

- [ ] **18.** cPanel → **Terminal**, go into the `@core` folder, run:

      php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear

      Skipping this is the most common reason a correct deploy looks broken.

- [ ] **19.** Open `https://hudumaportal.co.tz/system-upgrade` once.
      Safe to open again if you are unsure.

- [ ] **20.** Admin panel → **Rides → Ride Settings**. Confirm both switches
      are **off**. The fares there are placeholders, not real prices.

- [ ] **21.** Click through, logged out and logged in:
      home, a category, a service, search, register, log in, log out,
      buyer dashboard, seller dashboard, a test order to the payment screen,
      admin orders / services / users / settings.

- [ ] **22.** Turn maintenance mode off, if you turned it on at step 15.

---

## If it goes wrong

Restore the step 3 zip over the live folder, and import the step 4 database
export. Both are from the same moment, so they match each other.

---

## Afterwards, every change is this

    git checkout production && git merge --ff-only main && git push && git checkout main

Only changed files upload — seconds, not minutes. No maintenance mode.
Clear caches afterwards if the change touched config, routes or views.
