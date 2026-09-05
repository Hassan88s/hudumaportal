<style>
    /* ========== Professional Service Details Page ========== */
    :root{
        --sd-bg:#f5f7fb;
        --sd-card:#ffffff;
        --sd-border:#e8eaf2;
        --sd-border-soft:#f1f3f8;
        --sd-text:#1e293b;
        --sd-text-soft:#475569;
        --sd-muted:#64748b;
        --sd-soft:#94a3b8;
        --sd-primary:#ff6b2c;
        --sd-primary-dark:#e55621;
        --sd-primary-light:#fff1e8;
        --sd-success:#10b981;
        --sd-success-light:#ecfdf5;
        --sd-warning:#f59e0b;
        --sd-warning-light:#fffbeb;
        --sd-danger:#ef4444;
        --sd-info:#0ea5e9;
    }

    /* Page background — clean, neutral */
    .service-details-area{background:var(--sd-bg) !important;padding-top:32px !important;padding-bottom:60px !important}

    /* ===== LEFT COLUMN ===== */
    .service-details-wrapper{background:#fff;border:1px solid var(--sd-border);border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(15,23,42,0.04)}

    /* Service image */
    .details-thumb{margin:0 0 20px}
    .main-img-box{border-radius:12px;overflow:hidden;background:#0f172a;position:relative}
    .service-details-background-image{aspect-ratio:16/9;background-size:cover !important;background-position:center !important;background-repeat:no-repeat !important;border-radius:12px !important;display:block;width:100%}
    .single-slider .gallery-images{border-radius:12px;overflow:hidden}

    /* Seller info row */
    .author-tag.style-02{display:flex !important;align-items:center;gap:18px;flex-wrap:wrap;list-style:none;padding:16px 0 18px !important;margin:0 !important;border-bottom:1px solid var(--sd-border)}
    .author-tag .tag-list{list-style:none;display:inline-flex;align-items:center;gap:8px;padding:0;margin:0}
    .author-tag .authors{display:flex !important;align-items:center;gap:10px;text-decoration:none}
    .author-tag .authors .thumb{position:relative;width:40px;height:40px;border-radius:50%;overflow:hidden;background:#f1f5f9;flex-shrink:0;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,0.08)}
    .author-tag .authors .thumb img{width:100%;height:100%;object-fit:cover}
    .author-tag .authors .thumb .notification-dot{position:absolute;bottom:-1px;right:-1px;width:11px;height:11px;border-radius:50%;background:var(--sd-success);border:2px solid #fff}
    .author-tag .authors .author-title{font-size:14px !important;font-weight:700 !important;color:var(--sd-text) !important;line-height:1.2}
    .author-tag .reviews{font-size:13px !important;color:var(--sd-text) !important;font-weight:700;display:inline-flex;align-items:center;gap:4px}
    .author-tag .reviews i{color:var(--sd-warning) !important;font-size:13px}
    .author-tag .icon{font-size:12px;color:var(--sd-muted);font-weight:500;margin-right:6px;text-transform:uppercase;letter-spacing:0.4px}
    .seller-verified{background:#3b82f6 !important;color:#fff !important;font-size:9px !important;width:16px !important;height:16px !important;border-radius:50%;display:inline-flex !important;align-items:center;justify-content:center;flex-shrink:0}
    .seller-verified i{font-size:8px}

    /* Bookmark button */
    .bookmark-btn{padding:7px 14px !important;border-radius:8px !important;font-size:12px !important;font-weight:600 !important;border:1.5px solid var(--sd-border) !important;background:#fff !important;color:var(--sd-text) !important;transition:all .15s !important;cursor:pointer;line-height:1.4}
    .bookmark-btn.btn-outline-primary{border-color:var(--sd-border) !important;color:var(--sd-muted) !important}
    .bookmark-btn.btn-outline-primary:hover{background:var(--sd-primary-light) !important;color:var(--sd-primary) !important;border-color:var(--sd-primary-light) !important}
    .bookmark-btn.btn-warning{background:var(--sd-warning-light) !important;border-color:#fde68a !important;color:#92400e !important}
    .bookmark-btn.btn-warning:hover{background:var(--sd-warning) !important;border-color:var(--sd-warning) !important;color:#fff !important}

    /* Watch Video button */
    .video-btn{margin-left:auto}
    .video-btn .btn-wrapper{margin:0 !important}
    .video-btn .cmn-btn.btn-bg-1{padding:8px 16px !important;background:#0f172a !important;color:#fff !important;border:none !important;border-radius:8px !important;font-size:12px !important;font-weight:600 !important;text-decoration:none;display:inline-flex !important;align-items:center;gap:6px;transition:all .15s;margin-top:0 !important}
    .video-btn .cmn-btn.btn-bg-1::before{content:"▶";font-size:9px}
    .video-btn .cmn-btn.btn-bg-1:hover{background:#1e293b !important;transform:translateY(-1px)}

    /* Tabs — clean underline style */
    .details-tabs.tabs{display:flex !important;gap:0 !important;flex-wrap:wrap;list-style:none;margin:24px 0 0 !important;padding:0 !important;border-bottom:1px solid var(--sd-border) !important;background:transparent !important;border-radius:0 !important}
    .details-tabs.tabs .list{padding:12px 18px !important;font-size:14px !important;font-weight:600 !important;color:var(--sd-muted) !important;cursor:pointer;border-radius:0 !important;border:none !important;border-bottom:2px solid transparent !important;transition:all .15s;text-decoration:none;display:inline-block;line-height:1.2;background:transparent !important;margin-bottom:-1px}
    .details-tabs.tabs .list:hover{color:var(--sd-text) !important;background:transparent !important}
    .details-tabs.tabs .list.active{background:transparent !important;color:var(--sd-primary) !important;border-color:transparent !important;border-bottom-color:var(--sd-primary) !important;box-shadow:none !important;font-weight:700 !important}

    /* Tab content */
    .tab-content.another-tab-content{padding-top:24px}
    .details-content-tab{padding-top:0 !important}
    .details-tap-para{font-size:14px;color:var(--sd-text-soft);line-height:1.7;margin:0}
    .details-tap-para p{margin-bottom:12px}
    .details-tap-para p:last-child{margin-bottom:0}

    /* Section titles inside tab */
    .overview-single.style-02 .title,
    .faq-area .title,
    .section-title-two .title,
    .another-details-wrapper .title{font-size:18px !important;font-weight:700 !important;color:var(--sd-text) !important;margin:0 0 16px !important;line-height:1.3}

    /* Available Packages list */
    .overview-single.style-02{margin-top:28px;padding-top:24px !important;border-top:1px solid var(--sd-border)}
    .overview-benefits{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
    .overview-benefits .list{padding:12px 16px;background:#fafbfd;border:1px solid var(--sd-border-soft);border-radius:10px;font-size:13.5px;color:var(--sd-text);display:flex;align-items:center;gap:10px;transition:all .15s;margin:0}
    .overview-benefits .list:hover{border-color:var(--sd-primary) !important;background:var(--sd-primary-light)}
    .overview-benefits .list::before{content:"✓";display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;background:var(--sd-success);color:#fff;border-radius:50%;font-size:11px;font-weight:700;flex-shrink:0}
    .overview-benefits .list a{color:var(--sd-text) !important;text-decoration:none;font-weight:500}

    /* FAQ */
    .faq-area{margin-top:28px;padding-top:24px !important;padding-bottom:0 !important;border-top:1px solid var(--sd-border)}
    .faq-area .container{padding:0;margin:0}
    .faq-contents{display:flex;flex-direction:column;gap:8px;margin-top:12px}
    .faq-item{background:#fafbfd;border:1px solid var(--sd-border-soft);border-radius:10px;padding:14px 18px;transition:all .15s}
    .faq-item:hover{border-color:var(--sd-border)}
    .faq-title{font-size:14px !important;font-weight:700 !important;color:var(--sd-text) !important;margin:0 0 6px !important}
    .faq-panel,.faq-para{font-size:13px;color:var(--sd-text-soft);margin:0;line-height:1.6}

    /* Section title for related/recent services */
    .section-title-two{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-top:8px}
    .section-title-two .title{margin:0 !important}
    .section-title-two .section-btn{font-size:13px;color:var(--sd-primary);font-weight:700;text-decoration:none;padding:6px 14px;background:var(--sd-primary-light);border-radius:8px;transition:all .15s}
    .section-title-two .section-btn:hover{background:var(--sd-primary);color:#fff}
    .another-details-wrapper{margin-top:32px;padding-top:28px !important;border-top:1px solid var(--sd-border)}

    /* Service card (related / recent) */
    .single-service{background:#fff !important;border:1px solid var(--sd-border);border-radius:12px;overflow:hidden;transition:all .2s;height:100%;box-shadow:0 1px 2px rgba(15,23,42,0.04)}
    .single-service:hover{transform:translateY(-2px);border-color:var(--sd-border);box-shadow:0 8px 20px rgba(15,23,42,0.08)}
    .service-thumb{display:block;position:relative;aspect-ratio:16/10;background-size:cover !important;background-position:center !important;background-color:#fafbfd;border-radius:0 !important}
    .single-service .service-thumb{position:relative;overflow:hidden}
    .award-icons{position:absolute !important;top:10px !important;left:10px !important;right:auto !important;bottom:auto !important;z-index:5;display:inline-block !important;text-align:left !important;width:auto !important}
    .award-icons .badge.bg-warning{background:linear-gradient(135deg,#fbbf24,#f59e0b) !important;color:#fff !important;font-size:10px !important;font-weight:700 !important;padding:4px 10px !important;border-radius:6px !important;text-transform:uppercase;letter-spacing:0.4px;display:inline-block;box-shadow:0 2px 6px rgba(245,158,11,0.3)}
    .country_city_location{position:absolute;bottom:10px;left:10px;z-index:2}
    .country_city_location .single_location{background:rgba(15,23,42,0.85);color:#fff;font-size:11px;font-weight:600;padding:5px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;text-decoration:none;backdrop-filter:blur(4px)}
    .country_city_location .single_location i{font-size:11px}

    .single-service .services-contents{padding:14px 16px 16px !important}
    .single-service .author-tag{display:flex !important;justify-content:space-between;align-items:center;gap:10px;list-style:none;margin:0 0 10px !important;padding:0 !important;border:none !important;flex-wrap:wrap}
    .single-service .author-tag .tag-list{padding:0 !important}
    .single-service .author-tag .authors{display:flex !important;align-items:center;gap:7px;text-decoration:none}
    .single-service .author-tag .authors .thumb{width:26px;height:26px;border:none;box-shadow:none}
    .single-service .author-tag .author-title{font-size:12px !important;color:var(--sd-text) !important;font-weight:600 !important}
    .single-service .common-title{font-size:14px !important;font-weight:700 !important;color:var(--sd-text) !important;margin:0 0 6px !important;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:38px}
    .single-service .common-title a{color:var(--sd-text) !important;text-decoration:none}
    .single-service .common-title a:hover{color:var(--sd-primary) !important}
    .single-service .common-para{font-size:12px !important;color:var(--sd-muted) !important;line-height:1.5;margin:0 0 10px !important;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .single-service .service-price{display:flex;align-items:baseline;gap:6px;padding-bottom:10px;border-bottom:1px solid var(--sd-border-soft);margin-bottom:10px}
    .single-service .service-price .starting{font-size:10px;color:var(--sd-muted);font-weight:500;text-transform:uppercase;letter-spacing:0.3px}
    .single-service .service-price .prices{font-size:16px !important;font-weight:800 !important;color:var(--sd-primary) !important;line-height:1}
    .single-service .btn-wrapper{margin:0 !important}
    .single-service .cmn-btn.btn-small.btn-bg-1{display:inline-block;padding:8px 18px !important;background:var(--sd-primary) !important;color:#fff !important;border:none !important;border-radius:8px !important;font-size:12px !important;font-weight:700 !important;text-decoration:none;transition:all .15s}
    .single-service .cmn-btn.btn-small.btn-bg-1:hover{background:var(--sd-primary-dark) !important}

    /* ===== RIGHT COLUMN — Unified Professional Sidebar ===== */
    .col-lg-4 .service-details-package{position:sticky !important;top:90px !important;background:#fff !important;border:1px solid var(--sd-border) !important;border-radius:16px !important;padding:0 !important;box-shadow:0 4px 24px rgba(15,23,42,0.06) !important;overflow:hidden}
    .single-packages{padding:24px !important}

    /* Price header */
    .package-price{display:flex !important;justify-content:space-between;align-items:center;list-style:none;padding:0 0 18px !important;margin:0 !important;border-bottom:1px solid var(--sd-border-soft)}
    .package-price li{font-size:14px !important;color:var(--sd-muted) !important;font-weight:500 !important}
    .package-price li:first-child{color:var(--sd-text-soft) !important;font-weight:500 !important;font-size:13px !important;text-transform:uppercase;letter-spacing:0.4px}
    .package-price li:last-child{color:var(--sd-text) !important;font-weight:800 !important;font-size:28px !important;line-height:1}

    /* Package details */
    .details-available-price{padding-top:18px !important;margin:0 !important}
    .tilte-available,.summery-title h6{font-size:13px !important;font-weight:700 !important;color:var(--sd-text) !important;margin:0 0 12px !important;text-transform:uppercase;letter-spacing:0.4px}
    .onlilne-special-list{list-style:none;padding:0;margin:0 0 12px;display:flex;flex-direction:column;gap:8px}
    .onlilne-special-list li{display:flex !important;align-items:center;gap:10px;font-size:13.5px !important;color:var(--sd-text-soft) !important;padding:8px 12px;background:#fafbfd;border-radius:8px;font-weight:500}
    .onlilne-special-list li i{color:var(--sd-primary) !important;font-size:14px;flex-shrink:0}
    .available-list{list-style:none;padding:0;margin:0 0 18px}
    .available-list li{position:relative;font-size:13px !important;color:var(--sd-text-soft) !important;padding:7px 0 7px 24px;line-height:1.5}
    .available-list li::before{content:"";position:absolute;left:0;top:13px;width:6px;height:6px;border-radius:50%;background:var(--sd-primary);box-shadow:0 0 0 3px var(--sd-primary-light)}

    /* Book Appointment & Chat buttons */
    .service-details-package .btn-wrapper{padding:0 !important;margin-top:16px !important;display:flex;flex-direction:column;gap:8px}
    .service-details-package .cmn-btn.btn-bg-1{display:block !important;width:100% !important;padding:14px 22px !important;background:var(--sd-primary) !important;color:#fff !important;border:none !important;border-radius:10px !important;font-size:15px !important;font-weight:700 !important;text-align:center !important;text-decoration:none;transition:all .15s !important;margin:0 !important;line-height:1.2}
    .service-details-package .cmn-btn.btn-bg-1:hover{background:var(--sd-primary-dark) !important;transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,107,44,0.3)}
    .service-details-package br{display:none}
    .service-details-package .cmn-btn.live-chat-button-class-for-style,
    .service-details-package .cmn-btn.chat-toggle{background:#fff !important;color:var(--sd-text) !important;border:1.5px solid var(--sd-border) !important;display:flex !important;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px 22px !important;font-size:14px !important;font-weight:600 !important;border-radius:10px !important;margin:0 !important}
    .service-details-package .cmn-btn.live-chat-button-class-for-style:hover,
    .service-details-package .cmn-btn.chat-toggle:hover{background:#fafbfd !important;border-color:var(--sd-primary) !important;color:var(--sd-primary) !important}
    .service-details-package .cmn-btn.live-chat-button-class-for-style i,
    .service-details-package .cmn-btn.chat-toggle i{color:var(--sd-primary)}

    /* UNIFIED trust badges row at bottom of sidebar */
    .col-lg-4 .order-pagkages{display:flex !important;align-items:stretch;gap:0 !important;padding:0 !important;margin:0 !important;background:#fafbfd !important;border-top:1px solid var(--sd-border)}
    .col-lg-4 .order-pagkages .single-order{flex:1;display:flex !important;flex-direction:column;align-items:center;justify-content:center;gap:4px;padding:16px 12px !important;border-radius:0 !important;font-size:11px !important;font-weight:600 !important;color:var(--sd-muted) !important;background:transparent !important;border:none !important;text-align:center;text-transform:uppercase;letter-spacing:0.4px;line-height:1.3;border-right:1px solid var(--sd-border) !important}
    .col-lg-4 .order-pagkages .single-order:last-child{border-right:none !important}
    .col-lg-4 .order-pagkages .single-order i{font-size:18px !important;color:var(--sd-primary) !important;margin-bottom:2px;background:transparent !important;width:auto !important;height:auto !important;box-shadow:none !important;display:inline-block !important}
    .col-lg-4 .order-pagkages .single-order:first-child i{color:var(--sd-success) !important}

    /* Modal styling for video */
    .modal-content{border-radius:16px !important;overflow:hidden;border:none !important;box-shadow:0 24px 64px rgba(0,0,0,0.2)}
    .modal-body{padding:0 !important;background:#000}
    .modal-body iframe{width:100% !important;height:480px !important;border:none;display:block}
    .modal-footer{border-top:1px solid var(--sd-border) !important;padding:14px 22px !important;background:#fff}
    .modal-footer .btn-secondary{background:#64748b !important;border:none !important;padding:9px 20px !important;border-radius:8px !important;font-weight:600 !important;font-size:13px !important;color:#fff !important}

    /* Mobile */
    @media(max-width:991px){
        .col-lg-4 .service-details-package{position:static !important}
        .service-details-wrapper{padding:18px}
        .overview-benefits{grid-template-columns:1fr}
    }
    @media(max-width:768px){
        .service-details-area{padding-top:20px !important;padding-bottom:36px !important}
        .author-tag.style-02{gap:12px}
        .details-tabs.tabs{overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch}
        .details-tabs.tabs::-webkit-scrollbar{display:none}
        .details-tabs.tabs .list{padding:10px 14px !important;font-size:13px !important;white-space:nowrap;flex-shrink:0}
        .package-price li:last-child{font-size:24px !important}
        .service-details-package .cmn-btn.btn-bg-1{padding:12px 18px !important;font-size:14px !important}
    }
</style>
