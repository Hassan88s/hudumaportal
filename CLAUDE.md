# Huduma Portal — Claude Code Guide

## Project Overview

**Huduma Portal** (hudumaportal.co.tz) is a Laravel 9 freelance service marketplace for Tanzania — similar to Fiverr. Sellers list services, buyers purchase them, with full order management, payments, messaging, and live streaming.

- **Framework:** Laravel 9 / PHP 8.1 / MySQL 8.0
- **Frontend:** Blade templates + Livewire 2.12 + jQuery/Bootstrap
- **Real-time:** Pusher (WebSockets), Agora (video streaming)
- **Payments:** 10+ gateways (Stripe, PayPal, Razorpay, Paystack, Flutterwave, etc.)
- **Hosting:** cPanel / Apache (production)

## ⚠️ IMPORTANT: Production Workflow

This project runs on production. The local copy is a **backup for development**. After any change:

1. Make edits locally in `E:\Hudumaportal`
2. Test on local (http://localhost:8000)
3. Give the user a **clear list of every file changed** with full paths
4. User manually uploads changed files to the production server

**Never** assume changes auto-deploy. Always track and report modified files.

## Project Structure

```
E:\Hudumaportal\
├── index.php                   # Entry point (bootstraps Laravel from @core)
├── .htaccess                   # Apache rewrite rules
├── @core/                      # Full Laravel application
│   ├── app/
│   │   ├── Http/Controllers/   # Frontend/, Auth/, Api/, User/
│   │   ├── Libraries/Agora/    # Agora RTC/RTM token builders
│   │   ├── *.php               # 100+ Eloquent models
│   ├── resources/views/
│   │   ├── frontend/           # Public pages
│   │   ├── backend/            # Admin panel
│   │   └── frontend/live.blade.php  # Live stream page (standalone)
│   ├── routes/
│   │   ├── web.php             # Frontend public routes
│   │   ├── admin.php           # Admin panel
│   │   ├── seller.php          # Seller dashboard
│   │   ├── buyer.php           # Buyer dashboard
│   │   └── api.php             # API endpoints
│   ├── Modules/                # JobPost, LiveChat, Subscription, Wallet
│   └── .env                    # Environment config (DB, API keys, etc.)
├── assets/                     # CSS, JS, images
│   └── frontend/
│       ├── css/line-awesome.min.css    # Icons (use this locally, not CDN)
│       └── js/AgoraRTC_N-4.24.0.js     # Agora RTC SDK
├── security/                   # Separate security admin panel (own DB)
├── sitemap/                    # Auto-generated XML sitemaps
└── hudumaportalco_new.sql      # Database dump
```

## Local Development Setup (XAMPP)

The project runs locally via PHP's built-in server, not XAMPP Apache (because XAMPP htdocs is on `D:\`, project is on `E:\`).

**Start the local server:**
```bash
cd "E:/Hudumaportal" && php -S localhost:8000 -t .
```

Access at: **http://localhost:8000**

Database: `hudumaportalco_new` imported into local MySQL (XAMPP's MySQL, root user, empty password).

## 🚫 Files Modified for Local — DO NOT Upload to Production

These files are edited for local-only operation and must NEVER be pushed to the server:

| File | Local Change | Production State |
|------|-------------|------------------|
| `index.php` | Security includes commented out (lines 4-6) | Security includes active |
| `@core/.env` | `APP_URL=http://localhost:8000` | `APP_URL=https://hudumaportal.co.tz` |
| `.htaccess` | www redirect removed, cPanel session path commented | Production rules active |
| `@core/app/Providers/AppServiceProvider.php` | `URL::forceScheme('https')` commented out | HTTPS forced |

## Key Features

### Live Streaming (Agora)
- **Routes:** `/live` (audience+host), `/seller/start-stream`, POST `/seller/end-stream`, GET `/agora/token`, GET `/agora/rtm-token`
- **Controllers:**
  - `FrontendController::live_stream()` — main /live route, creates channels
  - `Frontend\AgoraController` — generates RTC + RTM tokens
  - `Frontend\SellerController::start_stream()` / `end_stream()` — seller dashboard
- **Model:** `App\AgoraStream` (table: `agora_streams`, columns: channel_name, host_id, title, description, is_live, started_at, ended_at)
- **Views:**
  - `resources/views/frontend/live.blade.php` — standalone streaming page (no master layout), modern dark UI
  - `resources/views/frontend/user/seller/stream/index.blade.php` — pre-stream setup page (title, desc, camera preview, stream history)
- **Agora config:** `@core/.env` — `AGORA_APP_ID`, `AGORA_APP_CERTIFICATE`, `AGORA_EXPIRY_TIME`
- **Features:** Host+audience roles, waiting room (join approval), chat, emoji reactions, screen share, mic/cam toggle, camera switch, fullscreen, share link with copy

### User Roles
Buyer, Seller, Admin, Enterprise — managed via Spatie Permission package.

### Payments
10+ gateways configured in `.env` (Stripe, PayPal, Razorpay, Paystack, Flutterwave, Mollie, Paytm, Midtrans, PayFast, Instamojo, MercadoPago).

## Common Commands

```bash
# Clear all Laravel caches (run after config/view changes)
cd "E:/Hudumaportal/@core" && php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear

# Run a raw SQL query via tinker
cd "E:/Hudumaportal/@core" && php artisan tinker --execute="\DB::statement(\"YOUR SQL HERE\");"

# Check current DB connection
cd "E:/Hudumaportal/@core" && php artisan tinker --execute="echo \DB::connection()->getDatabaseName();"
```

## Database Schema Notes

- **Main DB:** `hudumaportalco_new` (87 tables)
- **Security DB:** `hudumaportalco_security` (separate, production-only, security module)
- Key tables: `users`, `services`, `orders`, `categories` (3-level: category → subcategory → child_category), `blogs`, `agora_streams`, `jobs`, `wallets`, `support_tickets`
- User typically prefers **raw SQL** over Laravel migrations when making schema changes

## Icons / Assets

- **Line Awesome** (icons): Use local file `assets/frontend/css/line-awesome.min.css` — NOT the external CDN (maxst.icons8.com doesn't work reliably)
- **Agora SDKs:** `assets/frontend/js/AgoraRTC_N-4.24.0.js` and `assets/frontend/js/agora-rtm-2.2.2.min.js`

## Working with This Codebase

1. **Before making changes:** Read related files to understand existing patterns. The controllers have unusual indentation — preserve it.
2. **When editing controllers:** `SellerController.php` is ~4200 lines — search for the method you need.
3. **Permissions:** Always ask the user before making schema or config changes.
4. **Testing:** After changes, reload `http://localhost:8000` and verify visually. Use Chrome MCP tools for screenshots.
5. **Final step:** Always provide a clear list of changed files so the user can upload to production.
