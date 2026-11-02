# Huduma Portal — Pre-Production Manual Test Checklist

Automated coverage already proves the business logic (488 checks, see bottom).
**This list is only the things code cannot prove** — real money, real handsets,
real cameras, real inboxes, and how it all *looks*.

Test on **dev** (`https://dev.hudumaportal.co.tz`) unless stated. Mark each row.

Legend: ✅ pass · ❌ fail · ⬜ not run

---

## 1. Payments — the launch blocker

Use each gateway's **real sandbox/live credentials**. For every gateway, confirm
the order ends in the right state **and** that the seller only sees it once paid.

| # | Test | Expected | Result |
|---|------|----------|--------|
| 1.1 | Pay for a service with **ClickPesa** | Redirects to ClickPesa, returns, order `payment_status = complete`, seller is notified | ⬜ |
| 1.2 | Pay with **Stripe** | Same as above | ⬜ |
| 1.3 | Pay with **PayPal** | Same as above | ⬜ |
| 1.4 | Pay with **Flutterwave / Paystack** | Same as above | ⬜ |
| 1.5 | **Abandon** payment (close the tab at the gateway) | Order stays hidden from the seller; no notification sent | ⬜ |
| 1.6 | **Cash on delivery** order | Visible to seller immediately, marked COD | ⬜ |
| 1.7 | **Manual/bank** payment with receipt upload | Receipt stored, admin can approve | ⬜ |
| 1.8 | **Wallet** payment with sufficient balance | Balance reduced by exactly the order total | ⬜ |
| 1.9 | Wallet payment with **insufficient** balance | Refused, balance unchanged | ⬜ |
| 1.10 | **Refund / cancellation** of a paid order | Money returned, `payment_status = return` | ⬜ |
| 1.11 | **Subscription** purchase | Plan activates, expiry date correct | ⬜ |
| 1.12 | **Payout request** then admin marks Completed | Seller notified, balance correct, cannot re-withdraw the same funds | ⬜ |

> ⚠️ Pay particular attention to **1.5** — an unpaid order must never reach the seller.

---

## 2. SMS (NextSMS) — currently unproven end-to-end

Account is healthy (balance 456, credentials valid, numbers normalise correctly),
but **no SMS has been confirmed arriving on a handset**.

| # | Test | Expected | Result |
|---|------|----------|--------|
| 2.1 | `php artisan sms:diagnose <your real number>` on dev | SMS **arrives on the phone** | ⬜ |
| 2.2 | Register a new account with a real number | OTP SMS arrives | ⬜ |
| 2.3 | Register with a **local-format** number (`07...`) | Still arrives (normalised to `2557...`) | ⬜ |
| 2.4 | Check the NextSMS dashboard **delivery reports** | Status is *Delivered*, not just *Sent/Pending* | ⬜ |

> If 2.1 says "ACCEPTED" but nothing arrives, the **sender ID `Hudumapotal` is not
> TCRA-approved** on the account — that is the remaining suspect and it is outside
> our code.
>
> ⚠️ **Known by design:** while a user is *logged in* they get an in-app
> notification and **no SMS**. Testing while signed in will show zero SMS for
> order/cancellation events. Decide if you want this changed.

---

## 3. Live streaming (Agora)

| # | Test | Expected | Result |
|---|------|----------|--------|
| 3.1 | Seller starts a stream (`/seller/start-stream`) | Camera preview works, stream goes live | ⬜ |
| 3.2 | Viewer opens `/live` | Sees and hears the stream | ⬜ |
| 3.3 | Chat during the stream | Messages appear both ways | ⬜ |
| 3.4 | Mic / camera toggle, camera switch | Works | ⬜ |
| 3.5 | Screen share | Works | ⬜ |
| 3.6 | Waiting room / join approval | Host approves, viewer joins | ⬜ |
| 3.7 | End stream | Marked ended, appears under Previous Streams | ⬜ |
| 3.8 | On a real **phone** (4G, not wifi) | Acceptable quality | ⬜ |

---

## 4. Email — rendering and deliverability

| # | Test | Expected | Result |
|---|------|----------|--------|
| 4.1 | Order confirmation email | Logo visible, layout correct, button works | ⬜ |
| 4.2 | Open the same email in **Gmail app, Outlook, and Apple Mail** | Renders correctly in all three | ⬜ |
| 4.3 | Password reset email | Link works | ⬜ |
| 4.4 | Subscription expiry reminder | Days remaining **count down** (7, 5, 3, 1) and stop after expiry | ⬜ |
| 4.5 | Champions winner announcement | Correct name, prize, link | ⬜ |
| 4.6 | Check emails are **not in Spam** | Inbox, not junk (verify SPF/DKIM/DMARC) | ⬜ |
| 4.7 | Send a burst (~20 in a few minutes) | None throttled/blocked by cPanel | ⬜ |

---

## 5. Swahili — visual pass

Code now reports **0 missing keys** on the dashboards/availability/streaming/jobs
screens, but layout and DB-driven text still need eyes.

| # | Test | Expected | Result |
|---|------|----------|--------|
| 5.1 | Switch to Kiswahili, walk both dashboards | No English left; nothing overflows its box | ⬜ |
| 5.2 | My Availability, Start Streaming, Order Details | Fully translated | ⬜ |
| 5.3 | **Home → About** ("Authentic Reviews", "Top Customer Support") | These come from **admin/DB content**, not the language file — translate them in admin | ⬜ |
| 5.4 | **FAQ answers** | DB content — needs a Swahili version entered in admin | ⬜ |
| 5.5 | **Subscription** page ("months", button) | Check whether DB or code | ⬜ |
| 5.6 | **Service List** category names | Category names are DB rows — translate in admin | ⬜ |

> ⚠️ 5.3–5.6 **cannot be fixed by a language file**. They need per-language content
> in the admin panel (or a translation mechanism we'd have to build — tell me if you
> want that).

---

## 6. Roles & permissions (live)

| # | Test | Expected | Result |
|---|------|----------|--------|
| 6.1 | Create a limited role, assign to a test admin, log in **incognito** | Sidebar shows only permitted modules | ⬜ |
| 6.2 | As that admin, type a forbidden URL (e.g. general settings) | Bounced to dashboard with "no permission" | ⬜ |
| 6.3 | Super Admin | Full access, unaffected | ⬜ |
| 6.4 | Champions / Referral / Spin & Win menus | Hidden unless the role has the new permissions | ⬜ |

---

## 7. Spin & Win

| # | Test | Expected | Result |
|---|------|----------|--------|
| 7.1 | Run `_deploy/spin_wheel.sql`, clear caches, set status **ON** | Admin page saves | ⬜ |
| 7.2 | Spin as a logged-in buyer | **First spin always "Try Again"** | ⬜ |
| 7.3 | Second spin | Can win; a win shows a `SPIN-` code | ⬜ |
| 7.4 | Third attempt | Refused — no spins left | ⬜ |
| 7.5 | Log out / back in, try again | Still refused (tracked per account, not cookie) | ⬜ |
| 7.6 | Admin → Activity | Spins and winners listed | ⬜ |

---

## 8. Cross-cutting

| # | Test | Expected | Result |
|---|------|----------|--------|
| 8.1 | Whole site on a **real phone** | Layout fine, nothing cut off | ⬜ |
| 8.2 | Coming Soon mode ON, visit in **incognito** | Coming-soon page; registration still works; registered user sees queue page | ⬜ |
| 8.3 | Upload a large image / a PDF where allowed | Accepted or a clear error — never a 500 | ⬜ |
| 8.4 | Browser back/refresh after paying | No duplicate order, no double charge | ⬜ |
| 8.5 | Two tabs submitting the same action | No duplicate record | ⬜ |

---

## Automated coverage already in place (488 checks, 0 failing)

Payments & order lifecycle · coupons · tax · order status guards · cancellation ·
subscriptions · **payouts (incl. anti-double-spend)** · **wallet (deposit/confirm/deduct)** ·
roles & permissions · admin menus · OTP · verification · Coming Soon · Spin & Win ·
expiry reminders · Champions (points, penalties, rewards, missions, announce, reset,
wipe, loyalty, quality, engagement, referrals, HP notifications) · reviews · portfolio ·
profiles · onboarding · extra services · jobs (post→apply→hire) · **auth (register/login)** ·
**services (visibility/search)** · **messaging (incl. cross-user privacy)** · pages ·
pagination · data-leak checks.

### Bugs this sweep found and fixed
1. Champions could "announce" a season with **zero approved winners** (`acc0b6f`)
2. Prizes & Budget admin page had **no sub-navigation** (`4981d91`)
3. **Wallet: negative deduction increased the balance** — free money (`68d34c3`)
4. **Wallet: deposit confirmation was replayable** — double credit (`68d34c3`)
5. **Wallet: non-numeric amount caused a 500** instead of validation (`68d34c3`)
6. **Unapproved/switched-off services were publicly reachable by URL** (`20d1955`)
