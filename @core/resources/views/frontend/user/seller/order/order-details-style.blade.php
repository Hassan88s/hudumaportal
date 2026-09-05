<style>
/* ========================================================
   ORDER DETAILS — Professional Redesign (Orange Theme)
   Scoped to the order-details page only via .dashboard__inner
   ======================================================== */
.dashboard__inner {
    --od-primary: #ff6b2c;
    --od-primary-dark: #e55621;
    --od-primary-soft: #fff2eb;
    --od-primary-tint: #ffe9dd;
    --od-bg: #f5f7fb;
    --od-surface: #ffffff;
    --od-border: #ececf3;
    --od-border-soft: #f1f2f7;
    --od-text: #1f2433;
    --od-muted: #6b7280;
    --od-muted-2: #8b8fa3;
    --od-success: #1aae6f;
    --od-success-soft: #e6f7ef;
    --od-warning: #d98a00;
    --od-warning-soft: #fff4dc;
    --od-danger: #d9344b;
    --od-danger-soft: #fde7eb;
    --od-info: #2b86d9;
    --od-info-soft: #e2f0fb;
    --od-shadow: 0 6px 22px rgba(20, 24, 50, .06);
    --od-shadow-sm: 0 2px 8px rgba(20, 24, 50, .04);
}

/* Page background */
body { background: var(--od-bg) !important; }

/* ===== Outer card (the dashboard wrapper) ===== */
.dashboard__inner .dashboard_table__wrapper {
    background: var(--od-surface) !important;
    border: 1px solid var(--od-border) !important;
    border-radius: 16px !important;
    box-shadow: var(--od-shadow);
    padding: 24px !important;
}

/* ===== Inner action-bar card from partial ===== */
.dashboard__inner .card.shadow-sm {
    border: 1px solid var(--od-border) !important;
    border-radius: 14px !important;
    box-shadow: var(--od-shadow-sm) !important;
    background: var(--od-surface);
}
.dashboard__inner .card .card-body {
    padding: 22px 22px 6px;
}

/* ===== Section headings ===== */
.dashboard__inner h5.fw-bold,
.dashboard__inner .earning-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    color: var(--od-text) !important;
    letter-spacing: .2px;
    text-transform: uppercase;
    position: relative;
    padding-left: 12px;
    margin-bottom: 16px !important;
}
.dashboard__inner h5.fw-bold::before,
.dashboard__inner .earning-title::before {
    content: '';
    position: absolute;
    left: 0; top: 50%;
    transform: translateY(-50%);
    width: 4px; height: 16px;
    border-radius: 4px;
    background: linear-gradient(180deg, var(--od-primary), var(--od-primary-dark));
}

/* ===== Action bar (top status / action strip) ===== */
.dashboard__inner .action-bar > .p-2.border.rounded.bg-light {
    background: linear-gradient(135deg, #fff8f3 0%, #ffffff 70%) !important;
    border: 1px solid var(--od-primary-tint) !important;
    border-radius: 12px !important;
    padding: 12px 14px !important;
    gap: 8px !important;
    width: 100%;
    box-shadow: inset 0 0 0 1px rgba(255,107,44,.04);
}
.dashboard__inner .action-bar .vr {
    height: 22px;
    opacity: .35;
    background: var(--od-primary);
}

/* ===== Buttons ===== */
.dashboard__inner .btn {
    border-radius: 10px !important;
    font-weight: 600;
    font-size: 13px;
    padding: 8px 14px;
    letter-spacing: .2px;
    transition: all .18s ease;
    box-shadow: none;
}
.dashboard__inner .btn-sm { padding: 7px 12px; font-size: 12.5px; }

.dashboard__inner .btn-primary,
.dashboard__inner .btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--od-primary), var(--od-primary-dark)) !important;
    border-color: var(--od-primary-dark) !important;
    color: #fff !important;
    box-shadow: 0 6px 14px rgba(255,107,44,.28);
}
.dashboard__inner .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(255,107,44,.32);
}
.dashboard__inner .btn-outline-primary {
    background: var(--od-primary-soft) !important;
    border: 1px solid var(--od-primary-tint) !important;
    color: var(--od-primary-dark) !important;
}
.dashboard__inner .btn-success {
    background: var(--od-success) !important;
    border-color: var(--od-success) !important;
    color:#fff !important;
}
.dashboard__inner .btn-outline-success {
    background: var(--od-success-soft) !important;
    border: 1px solid #c6ecd9 !important;
    color: #128a55 !important;
}
.dashboard__inner .btn-outline-danger {
    background: var(--od-danger-soft) !important;
    border: 1px solid #f5c4cc !important;
    color: var(--od-danger) !important;
}
.dashboard__inner .btn-danger {
    background: var(--od-danger) !important;
    border-color: var(--od-danger) !important;
}
.dashboard__inner .btn-warning {
    background: var(--od-warning-soft) !important;
    border: 1px solid #f7e0a3 !important;
    color: #8a5a00 !important;
}
.dashboard__inner .btn-secondary {
    background: #2b3144 !important;
    border-color: #2b3144 !important;
    color:#fff !important;
}

/* ===== Badges ===== */
.dashboard__inner .badge {
    border-radius: 999px !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    padding: 7px 12px !important;
    letter-spacing: .2px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dashboard__inner .badge.bg-success {
    background: var(--od-success) !important; color:#fff !important;
}
.dashboard__inner .badge.bg-warning,
.dashboard__inner .badge.bg-warning.text-dark {
    background: var(--od-warning-soft) !important;
    color: #8a5a00 !important;
    border: 1px solid #f7e0a3;
}
.dashboard__inner .badge.bg-danger {
    background: var(--od-danger) !important; color:#fff !important;
}
.dashboard__inner .badge.bg-info-subtle {
    background: var(--od-info-soft) !important;
    color: var(--od-info) !important;
    border: 1px solid #c8e1f7;
}
.dashboard__inner .badge.bg-danger-subtle {
    background: var(--od-danger-soft) !important;
    color: var(--od-danger) !important;
    border: 1px solid #f5c4cc;
}
.dashboard__inner .badge.bg-warning-subtle {
    background: var(--od-warning-soft) !important;
    color: #8a5a00 !important;
    border: 1px solid #f7e0a3;
}
.dashboard__inner .badge.bg-secondary {
    background: #eef0f6 !important;
    color: #4b5063 !important;
    border: 1px solid var(--od-border);
}
.dashboard__inner .badge.bg-primary,
.dashboard__inner #race1, .dashboard__inner [id^='race'] {
    color: var(--od-primary-dark) !important;
}
.dashboard__inner [id^='race'] {
    background: var(--od-primary-soft);
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px dashed var(--od-primary-tint);
    font-variant-numeric: tabular-nums;
    font-size: 13px;
}

/* ===== Info rows (Name/Address/City etc.) ===== */
.dashboard__inner .row.g-2 > [class*='col-']  {
    padding: 10px 12px;
}
.dashboard__inner .row.g-2 > [class*='col-'] > .fw-semibold.text-secondary,
.dashboard__inner .d-flex.flex-wrap > div > .fw-semibold.text-secondary {
    display: inline-block;
    font-size: 11.5px !important;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: var(--od-muted-2) !important;
    margin-right: 6px;
}
.dashboard__inner .row.g-2 > [class*='col-'] > .fw-bold,
.dashboard__inner .d-flex.flex-wrap > div > .fw-bold {
    color: var(--od-text);
    font-weight: 600 !important;
    font-size: 14px;
}

/* Make Client Details / Amount Details / Payment Details visually distinct */
.dashboard__inner .card-body > .mb-4 {
    padding: 16px 18px;
    background: #fafbfd;
    border: 1px solid var(--od-border-soft);
    border-radius: 12px;
    margin-bottom: 14px !important;
}
.dashboard__inner .card-body > .mb-4:last-of-type,
.dashboard__inner .card-body > .mb-2 {
    margin-bottom: 0 !important;
}
.dashboard__inner .card-body > .mb-2 {
    padding: 16px 18px;
    background: #fafbfd;
    border: 1px solid var(--od-border-soft);
    border-radius: 12px;
}

/* hr divider removed - we use card boxes */
.dashboard__inner .card-body hr { display: none; }

/* Amount Details — emphasize the Total */
.dashboard__inner .row.g-2 > .col-md-4:last-child .fw-bold.fs-5 {
    display: inline-block;
    padding: 4px 12px;
    background: linear-gradient(135deg, var(--od-primary-soft), #fff);
    color: var(--od-primary-dark) !important;
    border-radius: 8px;
    border: 1px solid var(--od-primary-tint);
    font-size: 18px !important;
    font-weight: 800 !important;
}

/* ===== Tables (right column: Include, Additional, Extra, Coupon) ===== */
.dashboard__inner .single-flex-middle .line-charts-wrapper {
    background: var(--od-surface);
    border: 1px solid var(--od-border);
    border-radius: 14px;
    padding: 18px 18px 6px;
    box-shadow: var(--od-shadow-sm);
    margin-top: 0 !important;
}
.dashboard__inner .single-flex-middle + .single-flex-middle,
.dashboard__inner .single-flex-middle.mt-4 { margin-top: 16px !important; }

.dashboard__inner .line-charts-wrapper .line-top-contents { margin-top: 0 !important; }

.dashboard__inner table.table {
    border: none !important;
    margin-bottom: 4px;
}
.dashboard__inner table.table.table-bordered,
.dashboard__inner table.table.table-bordered td,
.dashboard__inner table.table.table-bordered th {
    border: 1px solid var(--od-border-soft) !important;
}
.dashboard__inner table.table thead th {
    background: #f7f8fc !important;
    color: var(--od-muted) !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: .6px;
    border-top: none !important;
    padding: 10px 12px;
}
.dashboard__inner table.table tbody td {
    padding: 11px 12px;
    vertical-align: middle;
    font-size: 13.5px;
    color: var(--od-text);
}
.dashboard__inner table.table tbody tr:hover td {
    background: #fafbfd;
}
.dashboard__inner table.table tbody tr:last-child td {
    background: var(--od-primary-soft) !important;
    border-top: 2px solid var(--od-primary-tint) !important;
}
.dashboard__inner table.table tbody tr:last-child strong {
    color: var(--od-primary-dark);
    font-weight: 800;
}
.dashboard__inner .info-text {
    display: inline-block;
    background: var(--od-warning-soft);
    color: #8a5a00;
    border: 1px solid #f7e0a3;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12.5px;
    font-weight: 500;
    margin-bottom: 14px !important;
}

/* Completion Request image preview */
.dashboard__inner .col-md-4 > .mt-3 h6 {
    font-size: 13px;
    font-weight: 700;
    color: var(--od-text);
    text-transform: uppercase;
    letter-spacing: .4px;
}
.dashboard__inner .col-md-4 > .mt-3 img {
    border-radius: 12px;
    border: 1px solid var(--od-border);
    box-shadow: var(--od-shadow-sm);
    max-width: 100%;
    height: auto;
}

/* Order Modifications History card */
.dashboard__inner .col-sm-12 .single-flex-middle .line-charts-wrapper {
    margin-top: 16px;
}

/* ===== Modal polish ===== */
.dashboard__inner ~ .modal .modal-content,
body > .modal .modal-content,
.modal#acceptExtraServiceModal .modal-content {
    border: none;
    border-radius: 14px;
    box-shadow: 0 16px 50px rgba(20,24,50,.22);
}
.modal#acceptExtraServiceModal .modal-header {
    background: linear-gradient(135deg, var(--od-primary), var(--od-primary-dark));
    color: #fff;
    border-radius: 14px 14px 0 0;
    padding: 14px 20px;
}
.modal#acceptExtraServiceModal .modal-header .close,
.modal#acceptExtraServiceModal .modal-header .btn-close {
    color: #fff;
    opacity: .85;
    text-shadow: none;
}
.modal#acceptExtraServiceModal .modal-title { font-weight: 700; }

/* ===== Responsive ===== */
@media (max-width: 991px) {
    .dashboard__inner .dashboard_table__wrapper { padding: 14px !important; }
    .dashboard__inner .card .card-body { padding: 14px; }
}

/* ====== MOBILE: flatten nested cards, stack action bar cleanly ====== */
@media (max-width: 767px) {
    /* Kill nested borders/shadows — show ONE outer card only */
    .dashboard__inner .dashboard_table__wrapper {
        padding: 12px !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 14px rgba(20,24,50,.05);
    }
    .dashboard__inner .card.shadow-sm {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        background: transparent !important;
    }
    .dashboard__inner .card .card-body { padding: 0 !important; }

    /* Sub-section cards: thinner, lighter */
    .dashboard__inner .card-body > .mb-4,
    .dashboard__inner .card-body > .mb-2 {
        padding: 12px 12px;
        border-radius: 10px;
        margin-bottom: 10px !important;
    }

    /* Action bar — full-width pills, no inner wrapper border */
    .dashboard__inner .action-bar > .p-2.border.rounded.bg-light {
        flex-direction: column;
        align-items: stretch !important;
        gap: 8px !important;
        padding: 12px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #fff8f3 0%, #fffdfb 100%) !important;
    }
    .dashboard__inner .action-bar .vr { display: none; }
    .dashboard__inner .action-bar .badge,
    .dashboard__inner .action-bar .btn,
    .dashboard__inner .action-bar [id^='race'] {
        display: flex !important;
        justify-content: center;
        align-items: center;
        width: 100%;
        text-align: center;
        padding: 10px 12px !important;
        font-size: 13px !important;
        border-radius: 10px !important;
        margin: 0 !important;
    }
    .dashboard__inner .action-bar [id^='race'] {
        font-size: 14px !important;
        font-weight: 700;
    }

    /* Typography */
    .dashboard__inner h5.fw-bold,
    .dashboard__inner .earning-title { font-size: 13px !important; margin-bottom: 12px !important; }
    .dashboard__inner .row.g-2 > [class*='col-']  { padding: 8px 10px; }
    .dashboard__inner .row.g-2 > [class*='col-'] > .fw-bold,
    .dashboard__inner .d-flex.flex-wrap > div > .fw-bold { font-size: 13px; }

    /* Tables stay readable */
    .dashboard__inner .single-flex-middle .line-charts-wrapper {
        padding: 12px;
        border-radius: 10px;
    }
    .dashboard__inner table.table tbody td,
    .dashboard__inner table.table thead th { padding: 9px 8px; font-size: 12.5px; }

    /* Payment / Order details flex blocks: stack with gap */
    .dashboard__inner .d-flex.flex-wrap.gap-4,
    .dashboard__inner .d-flex.flex-wrap.gap-5 {
        gap: 14px !important;
    }

    /* Total emphasis - smaller on mobile */
    .dashboard__inner .row.g-2 > .col-md-4:last-child .fw-bold.fs-5 {
        font-size: 16px !important;
        padding: 3px 10px;
    }
}

@media (max-width: 480px) {
    .dashboard__inner .dashboard_table__wrapper { padding: 10px !important; }
    .dashboard__inner .card-body > .mb-4,
    .dashboard__inner .card-body > .mb-2 { padding: 10px; }
}
</style>
