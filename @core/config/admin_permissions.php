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
            'admin-service-list', 'admin-service-add',
            'service-book-setting', 'service-detail-setting', 'service-create-setting',
            'coupon-list', 'service-review-manage', 'service-promote-manage',
        ],

        'Orders' => [
            'order-list', 'order-view', 'order-status', 'order-delete',
            'cancel-order-list', 'order-request-complete', 'extra-orders',
            'order-success-setting', 'order-create-setting', 'seller-buyer-report',
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
            'subscription-list', 'subscription-edit', 'subscription-delete',
            'subscription-settings', 'subscription-coupon-list',
            'subscription-reminder', 'seller-subscription-list',
        ],

        'Jobs' => [
            'job-list', 'job-status', 'job-delete',
        ],

        'Enterprise & Chat' => [
            'enterprise-manage', 'public-chat', 'all-seller',
        ],

        'Advertisements' => [
            'advertisement-list', 'advertisement-create', 'advertisement-edit', 'advertisement-delete',
        ],

        'Support Tickets' => [
            'ticket-list', 'ticket-view', 'ticket-delete',
        ],

        'Blog' => [
            'blog-list', 'blog-create', 'blog-edit', 'blog-delete', 'blog-detail-setting',
            'blog-single-settings',
            'blog-category-list', 'blog-category-create', 'blog-category-edit', 'blog-category-delete',
            'blog-tag-list', 'blog-tag-create', 'blog-tag-edit', 'blog-tag-delete',
            'blog-trashed-list', 'blog-trashed-edit', 'blog-trashed-restore', 'blog-trashed-delete',
        ],

        'Categories & Brands' => [
            'category-list', 'category-create', 'category-edit', 'category-delete', 'category-status',
            'subcategory-list', 'subcategory-create', 'subcategory-edit', 'subcategory-delete', 'subcategory-status',
            'brand-list', 'brand-create', 'brand-edit', 'brand-delete', 'brand-status',
        ],

        'Locations' => [
            'country-list', 'country-create', 'country-edit', 'country-delete', 'country-status',
            'city-list', 'city-create', 'city-edit', 'city-delete', 'city-status',
            'area-list', 'area-create', 'area-edit', 'area-delete', 'area-status',
        ],

        'Pages' => [
            'pages-list', 'pages-create', 'pages-edit', 'pages-delete',
            'page-settings-404-page-manage', 'page-settings-maintain-page-manage',
            'page-settings-contact-page-manage', 'page-settings-blog-page-manage',
        ],

        'Appearance' => [
            'appearance-media-image-manage',
            'appearance-menu-list', 'appearance-menu-create', 'appearance-menu-edit', 'appearance-menu-delete',
            'appearance-menubar-settings', 'appearance-topbar-settings', 'appearance-widget-builder',
            'appearance-home-variant',
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

        /*
         * Settings screens that hold third-party credentials or decide what
         * the public site shows. Each one was reachable by any admin role —
         * and most of them were already named in the sidebar or a controller
         * as a permission that had never been created, so the menu item was
         * invisible to every role except Super Admin while the URL stayed
         * open. Creating them makes both ends agree.
         */
        'Integrations & Site Config' => [
            'general-settings-payment-gateway-settings', 'general-settings-pusher',
            'general-settings-otp-settings', 'general-settings-adspace-settings',
            'general-settings-page-settings', 'gdpr-settings', 'company-settings',
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

        /*
        | Phase 2: the rest of the admin panel. Everything an admin role can
        | reach is now tied to a permission, except the few screens every
        | admin needs — the dashboard itself, dark mode, the dashboard layout
        | choice, their own profile and password, and the notification list
        | (which filters its own rows per permission instead).
        |
        | Where the sidebar or a controller already named a permission for a
        | screen, that name is used here even when a tidier one existed, so
        | that "the menu shows it" and "the URL opens" can never disagree.
        | Several of those names had no row in the permissions table at all;
        | the upgrade step creates them.
        */

        // The dashboard's AJAX feeds. The statistics area is hidden without
        // 'dashboard-stats', so the endpoints behind it must be too.
        'admin.home.visited.url.data'                => 'dashboard-stats',
        'admin.home.chart.data.by.device'            => 'dashboard-stats',
        'admin.home.chart.data.by.os'                => 'dashboard-stats',
        'admin.home.chart.data.by.country'           => 'dashboard-stats',
        'admin.home.chart.data.by.browser'           => 'dashboard-stats',

        // Notices shown to users across the site.
        'admin.all.notice'                           => 'notice-manage',
        'admin.add.notice.page'                      => 'notice-manage',
        'admin.add.notice'                           => 'notice-manage',
        'admin.notice.edit'                          => 'notice-manage',
        'admin.notice.update'                        => 'notice-manage',
        'admin.notice.status'                        => 'notice-manage',

        // Registration notices live with the other registration settings.
        'admin.seller.buyer.register.off.notice.update' => 'registration-settings',

        // Blog helpers and settings.
        'admin.get.tags.by.ajax'                     => 'blog-create',
        'admin.blog.details.settings.update'         => 'blog-detail-setting',
        'admin.blog.others.settings'                 => 'blog-detail-setting',

        // Child categories — the third taxonomy level.
        'admin.child.category'                       => 'category-list',
        'admin.child.category.new'                   => 'category-create',
        'admin.child.category.edit'                  => 'category-edit',
        'admin.child.category.status'                => 'category-status',
        'admin.child.category.bulk.action'           => 'category-delete',
        'admin.select.subcategory'                   => 'category-list',
        'admin.get.subcategory.by.category'          => 'category-list',

        // Fonts uploaded into the theme.
        'admin.custom.font.add'                      => 'general-settings-typography-settings',
        'admin.custom.font.css.update'               => 'general-settings-typography-settings',
        'admin.custom.font.status'                   => 'general-settings-typography-settings',
        'admin.custom.heading.font.status'           => 'general-settings-typography-settings',
        'admin.custom.typography.single'             => 'general-settings-typography-settings',

        // Enterprise account requests.
        'admin.enterprise.index'                     => 'enterprise-manage',
        'admin.enterprise.approve'                   => 'enterprise-manage',
        'admin.enterprise.reject'                    => 'enterprise-manage',

        // General settings screens that were left open.
        // Coming Soon sits under Reading in the menu and shares its gate.
        'admin.general.coming.soon'                  => 'general-settings-reading-settings',
        'admin.general.coming.soon.update'           => 'general-settings-reading-settings',
        'admin.general.adspace'                      => 'general-settings-adspace-settings',
        'admin.general.adsensecode'                  => 'general-settings-adspace-settings',
        'admin.general.updateadsspace.settings'      => 'general-settings-adspace-settings',
        'admin.general.update.refferal'              => 'referral-manage',
        'admin.general.CompanySetting'               => 'company-settings',
        'admin.general.update.CompanySetting'        => 'company-settings',
        'admin.general.global.payment.settings'      => 'general-settings-payment-gateway-settings',
        'admin.general.global.pusher.settings'       => 'general-settings-pusher',
        'admin.general.otp.settings'                 => 'general-settings-otp-settings',
        'admin.general.gdpr.settings'                => 'gdpr-settings',
        'admin.general.sitemap.settings'             => 'general-settings-seo-settings',
        'admin.general.license.key.generate'         => 'general-settings-licence-settings',
        'admin.general.update.version.check'         => 'general-settings-licence-settings',
        'admin.general.update.download.settings'     => 'general-settings-licence-settings',
        'admin.general.software.update.settings'     => 'general-settings-licence-settings',

        // Jobs. The menu puts settings and packages behind 'job-list' too.
        'admin.jobs.request.all'                     => 'job-list',
        'admin.jobs.request.conversation.details'    => 'job-list',
        'admin.job.settings'                         => 'job-list',
        'admin.job.create.settings.update'           => 'job-list',
        'admin.job.packages'                         => 'job-list',
        'admin.job_packages.update'                  => 'job-list',

        // Translations.
        'admin.languages.words.all'                  => 'language-edit',
        'admin.languages.clone'                      => 'language-edit',
        'admin.languages.add.new.word'               => 'language-edit',
        'admin.languages.regenerate.source.texts'    => 'language-edit',

        // Location settings and map coordinates.
        'admin.country.settings'                     => 'country-list',
        'admin.country.settings.update'              => 'country-list',
        'admin.country.coordinates.create'           => 'country-edit',
        'admin.country.coordinates.status'           => 'country-edit',

        // Menu builder helper.
        'admin.mega.menu.item.select.markup'         => 'appearance-menu-edit',

        /*
        | Orders. Each action takes the permission its own screen already
        | needs: the cancellations list is 'cancel-order-list', the order
        | success page is 'order-success-setting', and so on. Changing an
        | order's state or returning a buyer's money was open to every role.
        */
        'admin.change.order.status'                  => 'order-status',
        'admin.order.change.status'                  => 'order-status',
        'admin.cancel.pending.order'                 => 'cancel-order-list',
        'admin.orders.cancel.accept'                 => 'cancel-order-list',
        'admin.orders.cancel.reject'                 => 'cancel-order-list',
        'admin.orders.cancel.money.return'           => 'cancel-order-list',
        'admin.order.complete.request'               => 'order-request-complete',
        'admin.orders.decline.history'               => 'order-request-complete',
        'admin.extra.orders'                         => 'extra-orders',
        'admin.extra.order.complete.payment.status'  => 'extra-orders',
        'admin.order.seller.buyer.report'            => 'seller-buyer-report',
        'admin.order.report.chat.seller'             => 'seller-buyer-report',
        'admin.order.report.chat.buyer'              => 'seller-buyer-report',
        'admin.order.success.settings.update'        => 'order-success-setting',
        'admin.order.invoice.settings.update'        => 'order-success-setting',

        // Page builder — the front page is built here.
        'admin.page.builder.update'                  => 'pages-edit',
        'admin.page.builder.new'                     => 'pages-edit',
        'admin.page.builder.update.addon.order'      => 'pages-edit',
        'admin.page.builder.get.addon.markup'        => 'pages-edit',

        /*
        | Services. The admin's own services are a separate menu from the
        | sellers' listings, and the sidebar already calls that pair
        | 'admin-service-list' / 'admin-service-add' — the attribute and
        | category helpers those forms call go with the form. Coupons,
        | reviews and promotion are their own areas because each decides
        | what buyers pay or which listings get pushed to the front page.
        */
        'admin.all.services'                              => 'admin-service-list',
        'edit.service.history.list'                       => 'service-view',
        'admin.add.service'                               => 'admin-service-add',
        'admin.edit.service'                              => 'admin-service-add',
        'admin.add.service.attributes'                    => 'admin-service-add',
        'admin.add.service.attributes.by.id'              => 'admin-service-add',
        'admin.edit.service.attributes.by.id'             => 'admin-service-add',
        'admin.edit.service.attribute.offline.and.online' => 'admin-service-add',
        'admin.show.service.attributes.by.id'             => 'admin-service-add',
        'admin.get.subcategory'                           => 'admin-service-add',
        'admin.get.subcategory.with.child.category'       => 'admin-service-add',

        'admin.promotedservices'                          => 'service-promote-manage',
        'admin.promotedpayment.edit'                      => 'service-promote-manage',
        'admin.service.featured'                          => 'service-promote-manage',

        'admin.service.all.coupon.list'                   => 'coupon-list',
        'admin.service.seller.coupon.status'              => 'coupon-list',
        'admin.service.coupons'                           => 'coupon-list',
        'admin.service.coupon.edit'                       => 'coupon-list',
        'admin.service.coupon.status'                     => 'coupon-list',

        'service.all.service.reviews.list'                => 'service-review-manage',
        'admin.service.review.add'                        => 'service-review-manage',

        'admin.service.book.settings.update'              => 'service-book-setting',
        'admin.service.details.settings.update'           => 'service-detail-setting',
        'admin.service.login.register.settings'           => 'service-book-setting',
        'admin.login.register.settings.update'            => 'service-book-setting',
        'admin.service.create.settings'                   => 'service-create-setting',
        'admin.service.create.settings.update'            => 'service-create-setting',
        'admin.service.create.status.settings.update'     => 'service-create-setting',
        'admin.order.create.settings'                      => 'order-create-setting',
        'admin.order.create.settings.update'               => 'order-create-setting',
        'admin.service.load.more.category.settings'        => 'order-create-setting',
        'admin.service.load.more.category.settings.update' => 'order-create-setting',
        'admin.service.zone.settings'                      => 'order-create-setting',
        'admin.service.zone.settings.update'               => 'order-create-setting',
        'admin.service.zone'                               => 'order-create-setting',

        // Subscriptions.
        'admin.connect.settings'                     => 'subscription-list',
        'admin.subscription.coupon'                  => 'subscription-coupon-list',
        'admin.subscription.coupon.update'           => 'subscription-coupon-list',
        'admin.subscription.coupon.status'           => 'subscription-coupon-list',
        'admin.seller.subscription.history'          => 'seller-subscription-list',
        'admin.seller.subscription.buy'              => 'seller-subscription-list',
        'admin.seller.subscription.status'           => 'seller-subscription-list',
        'admin.seller.subscription.payment.status'   => 'seller-subscription-list',
        'admin.seller.subscription.bulk.action'      => 'seller-subscription-list',
        'admin.seller.subscription.email'            => 'seller-subscription-list',

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

        /*
        | Phase 2 sections. A prefix is used where a whole area shares one
        | permission, which also catches the unnamed POST routes that sit on
        | the same URL as their named GET (Laravel registers those twice, and
        | a nameless route can't be matched by route_map).
        */
        'admin-home/email-template'                        => 'general-settings-email-template',
        'admin-home/form-builder'                          => 'form-builder',
        'admin-home/page-builder'                          => 'pages-edit',
        'admin-home/chat-users'                            => 'public-chat',
        'admin-home/blog/others-settings'                  => 'blog-detail-setting',
        'admin-home/general-settings/otp-settings'         => 'general-settings-otp-settings',
        'admin-home/general-settings/gdpr-settings'        => 'gdpr-settings',
        'admin-home/general-settings/sitemap-settings'     => 'general-settings-seo-settings',
        'admin-home/general-settings/coming-soon'          => 'general-settings-reading-settings',
        'admin-home/general-settings/adspace'              => 'general-settings-adspace-settings',
        'admin-home/general-settings/CompanySetting'       => 'company-settings',
        'admin-home/general-settings/payment-gateway-settings' => 'general-settings-payment-gateway-settings',
        'admin-home/general-settings/pusher-settings'      => 'general-settings-pusher',
    ],

];
