<?php

/*
|--------------------------------------------------------------------------
| Admin permissions catalogue + route enforcement map
|--------------------------------------------------------------------------
|
| Single source of truth for the admin Roles & Permissions system. It is
| consumed by:
|   - database/seeds/RolePermissionSeeder.php  (seed the catalogue)
|   - AdminRoleManageController                 (group the role create/edit UI)
|   - App\Http\Middleware\AdminRoutePermission  (enforce access)
|
| "groups" lists every permission under a human label, in display order.
| Any permission that exists in the DB but is not listed here is shown in
| an "Other" group by the UI, so nothing is ever hidden.
|
| "route_map" maps a route NAME to the permission required to reach it.
| Only the route names listed here are enforced; every other admin route
| passes through untouched. Super Admin always bypasses. To expand
| enforcement later, just add more entries here — no route edits needed.
|
*/

return [

    'groups' => [

        // The admin home page. 'dashboard-stats' covers the whole statistics
        // area (counts, pending work, top services, order charts);
        // 'dashboard-finance' additionally covers the money on it.
        'Dashboard' => [
            'dashboard-stats',
        ],

        'User Management' => [
            'user-list', 'user-create', 'user-edit', 'user-delete',
        ],

        'Services' => [
            'service-list', 'service-view', 'service-status', 'service-delete',
            'service-book-setting', 'service-detail-setting',
        ],

        'Orders' => [
            'order-list', 'order-view', 'order-delete',
        ],

        'Payouts & Wallet' => [
            'payout-list', 'payout-view', 'payout-edit', 'payout-delete',
            'wallet-list', 'wallet-history',
        ],

        'Finance' => [
            'admin-commission', 'amount-settings', 'dashboard-finance', 'tax-manage',
        ],

        'Site Administration' => [
            'registration-settings', 'database-maintenance', 'notice-manage',
        ],

        'Subscriptions' => [
            'subscription-list', 'subscription-coupon-list',
            'subscription-reminder', 'seller-subscription-list',
        ],

        'Jobs' => [
            'job-list', 'job-status', 'job-delete',
        ],

        'Support Tickets' => [
            'ticket-list', 'ticket-view', 'ticket-delete',
        ],

        'Blog' => [
            'blog-list', 'blog-create', 'blog-edit', 'blog-delete', 'blog-detail-setting',
            'blog-tag-list', 'blog-tag-create', 'blog-tag-edit', 'blog-tag-delete',
            'blog-trashed-list', 'blog-trashed-restore', 'blog-trashed-delete',
        ],

        'Categories & Brands' => [
            'category-list', 'category-create', 'category-edit', 'category-delete', 'category-status',
            'subcategory-list', 'subcategory-create', 'subcategory-edit', 'subcategory-delete', 'subcategory-status',
            'brand-list', 'brand-create', 'brand-edit', 'brand-delete',
        ],

        'Locations' => [
            'country-list', 'country-create', 'country-edit', 'country-delete', 'country-status',
            'city-list', 'city-create', 'city-edit', 'city-delete', 'city-status',
            'area-list', 'area-create', 'area-edit', 'area-delete', 'area-status',
        ],

        'Pages' => [
            'pages-list', 'pages-create', 'pages-edit', 'pages-delete',
        ],

        'Appearance' => [
            'appearance-media-image-manage',
            'appearance-menu-list', 'appearance-menu-create', 'appearance-menu-edit', 'appearance-menu-delete',
            'appearance-menubar-settings', 'appearance-topbar-settings', 'appearance-widget-builder',
            'slider-list', 'slider-edit', 'slider-delete',
            'form-builder',
        ],

        'Languages' => [
            'language-list', 'language-create', 'language-edit', 'language-delete',
        ],

        'General Settings' => [
            'general-settings-basic-settings', 'general-settings-site-identity',
            'general-settings-color-settings', 'general-settings-typography-settings',
            'general-settings-email-settings', 'general-settings-smtp-settings',
            'general-settings-email-template', 'general-settings-reading-settings',
            'general-settings-seo-settings', 'general-settings-cache-settings',
            'general-settings-custom-css', 'general-settings-custom-js',
            'general-settings-third-party-scripts', 'general-settings-licence-settings',
            'general-settings-global-footer-settings', 'general-settings-global-navbar-settings',
        ],

        'Programs' => [
            'champions-manage', 'referral-manage', 'spin-wheel-manage',
        ],

    ],

    /*
    | Sensitive actions enforced first: deletes, payouts/wallet,
    | frontend-user management, and the general-settings pages.
    | (The /admin/* admin-account & role screens are already behind
    |  role:Super Admin, so they are intentionally not listed here.)
    */
    'route_map' => [

        // ---- Deletes -------------------------------------------------
        'admin.service.delete'                      => 'service-delete',
        'admin.services.additionalservice.delete'   => 'service-delete',
        'admin.services.includeservice.delete'       => 'service-delete',
        'admin.services.benifit.delete'              => 'service-delete',
        'admin.services.faq.delete'                  => 'service-delete',
        'admin.service.review.delete'                => 'service-delete',
        'admin.service.seller.coupon.delete'         => 'service-delete',
        'admin.coupon.delete'                        => 'service-delete',

        'admin.blog.delete.all.lang'                 => 'blog-delete',
        'admin.blog.category.delete.all.lang'        => 'blog-delete',
        'admin.blog.tags.delete.all.lang'            => 'blog-tag-delete',
        'admin.blog.trashed.delete'                  => 'blog-trashed-delete',

        'admin.category.delete'                      => 'category-delete',
        'admin.subcategory.delete'                   => 'subcategory-delete',
        'admin.child.category.delete'                => 'category-delete',
        'admin.brand.delete'                         => 'brand-delete',
        'admin.slider.delete'                        => 'slider-delete',

        'admin.country.delete'                       => 'country-delete',
        'admin.country.coordinates.delete'           => 'country-delete',
        'admin.country.tax.delete'                   => 'country-delete',
        'admin.city.delete'                          => 'city-delete',
        'admin.area.delete'                          => 'area-delete',

        'admin.page.delete'                          => 'pages-delete',
        'admin.page.delete.lang.all'                 => 'pages-delete',
        'admin.page.builder.delete'                  => 'pages-delete',
        'admin.form.builder.delete'                  => 'form-builder',

        'admin.order.report.delete'                  => 'order-delete',
        'admin.order.cancel.delete'                  => 'order-delete',
        'admin.ticket.delete'                        => 'ticket-delete',

        'admin.subscription.delete'                  => 'subscription-list',
        'admin.subscription.coupon.delete'           => 'subscription-coupon-list',
        'admin.seller.subscription.delete'           => 'seller-subscription-list',

        // ---- Payouts & Wallet ---------------------------------------
        'admin.payout.request.all'                   => 'payout-list',
        'admin.payout.request.view'                  => 'payout-view',
        'admin.payout.request.update'                => 'payout-edit',
        'admin.payout.request.delete'                => 'payout-delete',
        'admin.payout.request.bulk.action'           => 'payout-edit',
        'admin.wallet.lists'                         => 'wallet-list',
        'admin.wallet.history'                       => 'wallet-history',
        'admin.wallet.status'                        => 'wallet-list',
        'admin.wallet.history.status'                => 'wallet-history',
        'admin.wallet.deposit.create'                => 'wallet-list',

        // ---- Frontend user management -------------------------------
        'admin.frontend.user.delete'                 => 'user-delete',
        'admin.frontend.userdelete.status'           => 'user-delete',
        'admin.frontend.user.status'                 => 'user-edit',
        'admin.frontend.user.password'               => 'user-edit',
        'admin.user.info.update'                     => 'user-edit',
        'admin.all.frontend.user.bulk.action'        => 'user-edit',
        'admin.frontend.user.email.verify.code'      => 'user-edit',
        'admin.frontend.user.otp.verify.code'        => 'user-edit',
        'admin.frontend.seller.email.verify'         => 'user-edit',
        'admin.frontend.seller.otp.verify'           => 'user-edit',
        'admin.frontend.user.email.send.single'      => 'user-edit',

        // ---- General settings pages ---------------------------------
        'admin.general.basic.settings'               => 'general-settings-basic-settings',
        'admin.general.site.identity'                => 'general-settings-site-identity',
        'admin.general.color.settings'               => 'general-settings-color-settings',
        'admin.general.typography.settings'          => 'general-settings-typography-settings',
        'admin.general.email.settings'               => 'general-settings-email-settings',
        'admin.general.smtp.settings'                => 'general-settings-smtp-settings',
        'admin.general.smtp.settings.test'           => 'general-settings-smtp-settings',
        'admin.general.email.template'               => 'general-settings-email-template',
        'admin.general.reading'                      => 'general-settings-reading-settings',
        'admin.general.seo.settings'                 => 'general-settings-seo-settings',
        'admin.general.cache.settings'               => 'general-settings-cache-settings',
        'admin.general.custom.css'                   => 'general-settings-custom-css',
        'admin.general.custom.js'                    => 'general-settings-custom-js',
        'admin.general.license.settings'             => 'general-settings-licence-settings',
        'admin.general.global.variant.footer'        => 'general-settings-global-footer-settings',
        'admin.general.global.variant.navbar'        => 'general-settings-global-navbar-settings',

        /*
        | Phase 1: actions that move money, change what buyers are charged,
        | write to the database in bulk, or decide what sellers are told about
        | their payouts. Previously reachable by any admin role that knew the
        | URL, because hiding a menu item does not block a request.
        */

        // Commission and platform amounts.
        'admin.commission.update'                    => 'admin-commission',
        'admin.amount.settings'                      => 'amount-settings',
        'admin.amount.settings.update'               => 'amount-settings',

        // Who may register, and what they are told when closing an account.
        'admin.user.register.settings'               => 'registration-settings',
        'admin.seller.register.settings.update'      => 'registration-settings',
        'admin.buyer.register.settings.update'       => 'registration-settings',
        'admin.user.account.delete.request.update'   => 'registration-settings',

        // Tax — what buyers actually pay.
        'admin.country.tax'                          => 'tax-manage',
        'admin.country.tax.edit'                     => 'tax-manage',
        'admin.service.tax.update'                   => 'tax-manage',

        // Schema changes. Super Admin territory.
        'admin.database.update'                      => 'database-maintenance',
        'admin.database.update.run'                  => 'database-maintenance',
        'admin.general.database.upgrade'             => 'database-maintenance',

        // Bulk CSV writes straight into the location tables.
        'admin.import.csv.settings'                  => 'country-create',
        'admin.country.import'                       => 'country-create',
        'admin.country.import.database'              => 'country-create',
        'admin.import.city.csv.settings'             => 'city-create',
        'admin.city.import'                          => 'city-create',
        'admin.city.import.database'                 => 'city-create',
        'admin.import.area.csv.settings'             => 'area-create',
        'admin.area.import'                          => 'area-create',
        'admin.area.import.database'                 => 'area-create',
        'admin.area.import.country.city'             => 'area-create',

        // Deletions that were open to everyone.
        'admin.notification.delete'                  => 'notice-manage',
        'admin.delete.notice'                        => 'notice-manage',
        'admin.custom.delete.font.file'              => 'general-settings-typography-settings',
        'admin.general.sitemap.settings.delete'      => 'general-settings-seo-settings',

        // What sellers are told about their money.
        'admin.seller.payout.request.template'       => 'general-settings-email-template',
        'admin.seller.payout.request'                => 'general-settings-email-template',
        'admin.payment.withdraw.amount.email'        => 'general-settings-email-template',

    ],

    /*
    | Path-prefix enforcement — for whole sections that have many routes under
    | one URL prefix (so we don't have to list every route name). The middleware
    | blocks any admin-home request whose path starts with one of these prefixes
    | when the admin lacks the mapped permission. Super Admin still bypasses.
    */
    'path_map' => [
        'admin-home/general-settings/champions'            => 'champions-manage',
        'admin-home/general-settings/RefferalSetting'      => 'referral-manage',
        'admin-home/general-settings/referrals'            => 'referral-manage',
        'admin-home/general-settings/referral-rewards'     => 'referral-manage',
        'admin-home/general-settings/referral-clicks'      => 'referral-manage',
        'admin-home/general-settings/referrer-leaderboard' => 'referral-manage',
        'admin-home/general-settings/spin-wheel'           => 'spin-wheel-manage',

        // Catches the unnamed POST on these screens as well as the named GETs.
        'admin-home/general-settings/database-update'      => 'database-maintenance',
        'admin-home/general-settings/database-upgrade'     => 'database-maintenance',
        'admin-home/seller-settings/admin-commission'      => 'admin-commission',
        'admin-home/seller-settings/amount-settings'       => 'amount-settings',
        'admin-home/country/tax'                           => 'tax-manage',
    ],

];
