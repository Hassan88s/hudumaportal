<style>
    /* ========== Modern Service-List Page Redesign ========== */
    :root{
        --sl-bg:#f5f7fb;
        --sl-card:#ffffff;
        --sl-border:#e8eaf2;
        --sl-text:#1e293b;
        --sl-muted:#64748b;
        --sl-soft:#94a3b8;
        --sl-primary:#ff6b2c;
        --sl-primary-dark:#e55621;
        --sl-primary-light:#ffe9dd;
        --sl-success:#10b981;
        --sl-warning:#f59e0b;
        --sl-danger:#ef4444;
    }

    /* Page background — soft peach gradient for a warm professional feel */
    .new_services_area{background:linear-gradient(180deg,#fff5ee 0%,#f8f9fc 250px) !important;padding-top:32px !important;padding-bottom:60px !important;position:relative}
    .new_services_area::before{content:"";position:absolute;top:0;left:0;right:0;height:280px;background:radial-gradient(ellipse at top,rgba(255,107,44,0.06) 0%,transparent 70%);pointer-events:none;z-index:0}
    .new_services_area > *{position:relative;z-index:1}

    /* Sidebar filter container — softer, cleaner */
    .new_serviceDetails__side{background:transparent !important}
    .new_serviceDetails__side__item{background:#fff !important;border:1px solid #eef0f6 !important;border-radius:16px !important;padding:0 !important;box-shadow:0 4px 20px rgba(30,41,59,0.06);position:sticky;top:90px;overflow:hidden}

    /* Filter header */
    .service_filter_with_reset{display:flex !important;justify-content:space-between !important;align-items:center !important;gap:8px !important;padding:18px 20px !important;background:linear-gradient(135deg,#fff8f4 0%,#ffffff 100%);border-bottom:1px solid #eef0f6;margin:0 !important}
    .service_filter_with_reset .common-title{font-size:16px !important;font-weight:700 !important;color:var(--sl-text) !important;margin:0 !important;line-height:1.2;display:inline-flex;align-items:center;gap:8px}
    .service_filter_with_reset .common-title::before{content:"\f0b0";font-family:"Font Awesome 6 Free","Font Awesome 5 Free","FontAwesome";font-weight:900;color:var(--sl-primary);font-size:14px}
    .service_filter_with_reset a{text-decoration:none}
    .service_filter_with_reset .text-danger{color:var(--sl-primary) !important;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;cursor:pointer;padding:5px 10px;background:rgba(255,107,44,0.08);border-radius:20px;transition:all .15s;display:inline-block}
    .service_filter_with_reset .text-danger:hover{background:var(--sl-primary) !important;color:#fff !important;text-decoration:none}

    /* Individual filter sections — softer dividers, cleaner spacing */
    .new_serviceDetails__side__item .new_serviceDetails__side__author{border-bottom:1px solid #f1f3f8;padding:16px 20px !important;margin:0 !important;background:transparent}
    .new_serviceDetails__side__item .new_serviceDetails__side__author:last-child{border-bottom:none}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title{margin:0 0 12px !important;font-size:13px !important;font-weight:700 !important;color:var(--sl-text) !important;text-transform:uppercase;letter-spacing:0.5px}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title a{color:var(--sl-text) !important;text-decoration:none !important;display:flex;justify-content:space-between;align-items:center;padding:0 !important;cursor:pointer;transition:color .15s}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title a:hover{color:var(--sl-primary) !important}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title a i{font-size:11px;color:var(--sl-muted);transition:transform .2s;background:#f1f3f8;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title a:hover i{background:var(--sl-primary-light);color:var(--sl-primary)}
    .new_serviceDetails__side__author__contents .new_packageBook__addFeature__title a[aria-expanded="false"] i{transform:rotate(-90deg)}

    /* Search input */
    .search-input,
    input.form-control.search-input,
    .single-select input.search-input{width:100% !important;background:#fafbfd !important;border:1px solid var(--sl-border) !important;border-radius:10px !important;padding:10px 14px !important;font-size:13px !important;color:var(--sl-text) !important;outline:none !important;transition:all .15s !important;box-shadow:none !important;height:auto !important}
    .search-input:focus,
    input.form-control.search-input:focus{border-color:var(--sl-primary) !important;background:#fff !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12) !important}
    .search-input::placeholder{color:#a0aec0 !important}

    /* Native select (subcategory etc.) */
    select{width:100% !important;background:#fafbfd !important;border:1px solid var(--sl-border) !important;border-radius:10px !important;padding:10px 14px !important;font-size:13px !important;color:var(--sl-text) !important;outline:none !important;cursor:pointer;transition:all .15s !important}
    select:focus{border-color:var(--sl-primary) !important;background:#fff !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12) !important}
    .single-category-service .single-select{margin:0 !important;width:100%}
    .single-category-service .single-select select{width:100% !important}

    /* Select2 dropdown override */
    .select2-container--default .select2-selection--single{height:42px !important;border:1px solid var(--sl-border) !important;border-radius:10px !important;padding:5px 12px !important;background:#fafbfd !important}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:32px !important;color:var(--sl-text) !important;padding-left:0 !important}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:40px !important;right:8px !important}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{border-color:var(--sl-primary) !important;background:#fff !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12) !important}
    .select2-dropdown{border:1px solid var(--sl-border) !important;border-radius:10px !important;box-shadow:0 8px 24px rgba(0,0,0,0.08) !important;overflow:hidden}
    .select2-results__option--highlighted{background:var(--sl-primary) !important;color:#fff !important}

    /* Offline / Online / All buttons */
    .job_status_wise_section_start{display:flex;gap:6px;margin-bottom:14px !important;margin-top:0 !important;flex-wrap:wrap}
    .job_status_wise_section_start .btn{padding:7px 14px !important;font-size:12px !important;font-weight:600 !important;border-radius:8px !important;border:1px solid var(--sl-border) !important;transition:all .15s !important;cursor:pointer;flex:1;min-width:60px}
    .job_status_wise_section_start .btn-secondary{background:#fff !important;color:var(--sl-text) !important;border-color:var(--sl-border) !important}
    .job_status_wise_section_start .btn-secondary:hover{background:#fafbfd !important;border-color:var(--sl-primary) !important;color:var(--sl-primary) !important}
    .job_status_wise_section_start .btn-primary{background:var(--sl-primary) !important;color:#fff !important;border-color:var(--sl-primary) !important;box-shadow:0 2px 6px rgba(255,107,44,0.25)}

    /* Suburb / autocomplete address */
    .suburb_section_start label,
    .new_serviceDetails__side__author label{font-size:12px;font-weight:600;color:var(--sl-text);margin-bottom:6px;display:block}
    .suburb_section_start input{margin-bottom:4px}

    /* Distance slider */
    .slider-container{padding:8px 0 !important;margin:0 !important}
    .slider-container strong{font-size:12px;font-weight:600;color:var(--sl-text);margin-bottom:8px;display:block}
    #slider.slider-range{height:6px;background:#e2e8f0;border-radius:6px;margin:8px 0 14px;position:relative}
    #slider .noUi-connect,
    .noUi-connect{background:var(--sl-primary) !important;border-radius:6px}
    #slider .noUi-handle,
    .noUi-handle{background:#fff !important;border:2.5px solid var(--sl-primary) !important;border-radius:50% !important;width:18px !important;height:18px !important;box-shadow:0 2px 6px rgba(0,0,0,0.15) !important;cursor:grab;top:-7px !important;right:-9px !important}
    .noUi-handle::before,.noUi-handle::after{display:none}
    .noUi-handle:hover,.noUi-handle:active{background:var(--sl-primary) !important;border-color:var(--sl-primary-dark) !important}
    .slider-range-value{font-size:13px;font-weight:700;color:var(--sl-primary);margin-top:6px}
    .km_title_text{font-size:11px !important;color:var(--sl-muted) !important;font-weight:500}

    /* Price multi-range slider */
    .middle{padding:6px 0}
    #multi_range{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--sl-primary-light);color:var(--sl-primary);padding:10px 14px;border-radius:10px;font-size:14px;font-weight:700;margin-bottom:14px !important}
    #multi_range #currency strong{color:var(--sl-primary);font-weight:800}
    .multi-range-slider{position:relative;height:32px;display:flex;align-items:center}
    .multi-range-slider input[type="range"]{position:absolute;left:0;right:0;top:0;width:100%;height:32px;-webkit-appearance:none;appearance:none;background:transparent;pointer-events:none;outline:none;margin:0}
    .multi-range-slider input[type="range"]::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;width:18px;height:18px;background:#fff;border:2.5px solid var(--sl-primary);border-radius:50%;cursor:pointer;pointer-events:all;box-shadow:0 2px 6px rgba(0,0,0,0.15);margin-top:0}
    .multi-range-slider input[type="range"]::-moz-range-thumb{width:18px;height:18px;background:#fff;border:2.5px solid var(--sl-primary);border-radius:50%;cursor:pointer;pointer-events:all;box-shadow:0 2px 6px rgba(0,0,0,0.15)}
    .multi-range-slider .slider{position:absolute;width:100%;height:6px;background:#e2e8f0;border-radius:6px;top:50%;transform:translateY(-50%)}
    .multi-range-slider .track{position:absolute;width:100%;height:100%;background:transparent}
    .multi-range-slider .range{position:absolute;height:100%;background:var(--sl-primary);border-radius:6px}

    /* Filter Apply button */
    .submit-btn,
    button.submit-btn,
    button#distance_wise_filter_apply,
    button#price_wise_filter_apply{padding:8px 22px !important;background:var(--sl-primary) !important;border:none !important;border-radius:8px !important;color:#fff !important;font-size:13px !important;font-weight:700 !important;cursor:pointer !important;transition:all .15s !important;display:inline-block !important}
    .submit-btn:hover,
    button.submit-btn:hover{background:var(--sl-primary-dark) !important;transform:translateY(-1px);box-shadow:0 4px 10px rgba(255,107,44,0.25)}
    .cancel_apply_section_start{margin-top:10px !important;margin-bottom:8px !important}

    /* Mobile filter toggle button */
    .d-xl-none .btn-primary,
    button#mobileFilterToggle{background:var(--sl-primary) !important;border-color:var(--sl-primary) !important;font-weight:700 !important;border-radius:10px !important;padding:11px !important;font-size:14px !important;box-shadow:0 2px 6px rgba(255,107,44,0.2)}
    button#mobileFilterToggle:hover{background:var(--sl-primary-dark) !important;border-color:var(--sl-primary-dark) !important}

    /* ===== Service Cards ===== */
    .new_service__single{background:#fff !important;border:1px solid var(--sl-border) !important;border-radius:16px !important;overflow:hidden;transition:all .2s;height:100%;display:flex;flex-direction:column;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
    .new_service__single:hover{transform:translateY(-3px);border-color:var(--sl-primary-light) !important;box-shadow:0 8px 24px rgba(0,0,0,0.08) !important}

    /* Service thumbnail */
    .new_service__single__thumb{position:relative;background:#fafbfd;aspect-ratio:16/10;overflow:hidden}
    .new_service__single__thumb a{display:block;width:100%;height:100%}
    .new_service__single__thumb img{width:100% !important;height:100% !important;object-fit:cover;transition:transform .35s}
    .new_service__single:hover .new_service__single__thumb img{transform:scale(1.06)}

    /* Promoted badge */
    .award_icons{position:absolute;top:12px;left:12px;z-index:2}
    .award_icons .badge.bg-warning{background:linear-gradient(135deg,#fbbf24,#f59e0b) !important;color:#fff !important;font-size:10px !important;font-weight:700 !important;padding:5px 10px !important;border-radius:6px !important;text-transform:uppercase;letter-spacing:0.4px;box-shadow:0 2px 6px rgba(245,158,11,0.3)}

    /* Card content */
    .new_service__single__contents{padding:14px 16px 16px !important;display:flex;flex-direction:column;flex:1}

    /* Location pin */
    .new_jobs__single__contents__location{display:inline-flex !important;align-items:center;gap:5px;font-size:12px;color:var(--sl-primary);font-weight:600;margin-bottom:8px !important}
    .new_jobs__single__contents__location i{color:var(--sl-primary);font-size:11px}

    /* Service title */
    .new_service__single__contents__title{font-size:15px !important;font-weight:700 !important;color:var(--sl-text) !important;margin:0 0 10px !important;line-height:1.35 !important;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:42px}
    .new_service__single__contents__title a{color:var(--sl-text) !important;text-decoration:none}
    .new_service__single__contents__title a:hover{color:var(--sl-primary) !important}

    /* Price */
    .new_service__single__price{margin:0 0 12px !important;padding-bottom:12px;border-bottom:1px dashed var(--sl-border);display:flex;align-items:baseline;gap:6px;flex-wrap:wrap}
    .new_service__single__price__starting{font-size:11px !important;color:var(--sl-muted) !important;font-weight:500 !important;text-transform:uppercase;letter-spacing:0.3px}
    .new_service__single__price__title{font-size:18px !important;font-weight:800 !important;color:var(--sl-primary) !important;margin:0 !important;line-height:1.2}

    /* Author / seller info */
    .author_tag{display:flex !important;align-items:center;gap:8px;flex-wrap:wrap;margin:0 !important;padding:0 !important;border-top:none !important;background:transparent}
    .author_tag.border_top{border-top:none !important;padding-top:0 !important}
    .author_tag .single_authors{display:flex !important;align-items:center;gap:8px;text-decoration:none;flex:1;min-width:0;padding:0 !important;margin:0 !important}
    .single_authors__thumb{position:relative;width:30px;height:30px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#f1f5f9}
    .single_authors__thumb img{width:100%;height:100%;object-fit:cover}
    .single_authors__thumb .notification-dot{position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:var(--sl-success);border:2px solid #fff}
    .single_authors__title{font-size:13px !important;color:var(--sl-text) !important;font-weight:600 !important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px}
    .single_authors__title:hover{color:var(--sl-primary) !important}
    .seller-verified{color:#10b981;font-size:12px;background:#d1fae5;width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center}
    .seller-verified i{font-size:10px}

    /* PRO subscription badge - keep image, just clean up the wrapper */
    .author_tag .single_authors__thumb:not(:first-child){width:28px;height:28px;border-radius:6px;background:transparent}
    .author_tag .single_authors__thumb:not(:first-child) img{object-fit:contain}

    /* Rating */
    .author_tag__review{padding:3px 8px !important;background:#fef3c7 !important;border-radius:8px !important;display:inline-flex;align-items:center}
    .author_tag__review__para{font-size:11px !important;color:#92400e !important;font-weight:700 !important;text-decoration:none !important;display:inline-flex;align-items:center;gap:3px;line-height:1}
    .author_tag__review__para i{color:#f59e0b !important;font-size:10px}

    /* Book Now button */
    .new_service__single__contents .btn-wrapper{margin-top:auto !important;padding-top:12px !important;border-top:1px solid var(--sl-border) !important}
    .new_service__single__contents .btn-wrapper.border_top{border-top:1px solid var(--sl-border) !important;padding-top:12px !important}
    .new_service__single__contents .cmn-btn{padding:10px 16px !important;border-radius:10px !important;font-weight:700 !important;font-size:13px !important;text-decoration:none !important;text-align:center !important;display:inline-flex !important;align-items:center;justify-content:center;transition:all .15s !important;border:1.5px solid var(--sl-primary) !important;background:transparent !important;color:var(--sl-primary) !important}
    .new_service__single__contents .cmn-btn:hover{background:var(--sl-primary) !important;color:#fff !important;transform:translateY(-1px);box-shadow:0 4px 10px rgba(255,107,44,0.25)}
    .new_service__single__contents .cmn-btn.btn-outline-border{border:1.5px solid var(--sl-primary) !important;background:transparent !important;color:var(--sl-primary) !important}
    .new_service__single__contents .cmn-btn.btn-outline-border:hover{background:var(--sl-primary) !important;color:#fff !important}

    /* Pagination */
    .blog-pagination,.custom-pagination{margin-top:30px !important}
    .pagination{justify-content:center;gap:6px;flex-wrap:wrap;list-style:none;padding:0;display:flex}
    .pagination .page-item .page-link{background:#fff !important;border:1px solid var(--sl-border) !important;border-radius:10px !important;color:var(--sl-text) !important;padding:9px 14px !important;font-size:13px !important;font-weight:600 !important;transition:all .15s !important;text-decoration:none}
    .pagination .page-item .page-link:hover{background:var(--sl-primary-light) !important;border-color:var(--sl-primary) !important;color:var(--sl-primary) !important}
    .pagination .page-item.active .page-link{background:var(--sl-primary) !important;color:#fff !important;border-color:var(--sl-primary) !important;box-shadow:0 2px 6px rgba(255,107,44,0.25)}
    .pagination .page-item.disabled .page-link{background:#fafbfd !important;color:#cbd5e0 !important;border-color:var(--sl-border) !important;cursor:not-allowed}

    /* No services message */
    .common-title.text-danger{padding:32px;text-align:center;font-size:16px;font-weight:600;color:var(--sl-muted) !important;background:#fff;border:1px dashed var(--sl-border);border-radius:14px}

    /* Mobile responsive */
    @media(max-width:1199px){
        .new_serviceDetails__side__item{position:static}
    }
    @media(max-width:768px){
        .new_services_area{padding-top:20px !important;padding-bottom:32px !important}
        .new_service__single__contents{padding:12px}
        .new_service__single__contents__title{font-size:14px !important;min-height:38px}
        .new_service__single__price__title{font-size:16px !important}
    }
</style>
