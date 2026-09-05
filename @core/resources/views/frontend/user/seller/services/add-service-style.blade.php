<style>
    /* ========== Modern Add Service Page Redesign ========== */
    :root{
        --as-bg:#f5f7fb;
        --as-card:#ffffff;
        --as-border:#e8eaf2;
        --as-text:#1e293b;
        --as-muted:#94a3b8;
        --as-primary:#ff6b2c;
        --as-primary-dark:#e55621;
        --as-primary-light:#ffe9dd;
        --as-success:#10b981;
        --as-warning:#f59e0b;
        --as-info:#0ea5e9;
        --as-danger:#ef4444;
    }

    /* Page background */
    .dashboard__body{background:var(--as-bg)}

    /* Main wrapper card */
    .dashboard__inner__item{background:#fff;border:1px solid var(--as-border);border-radius:16px;padding:24px 28px;box-shadow:0 1px 4px rgba(0,0,0,0.03)}

    /* Page title */
    .dashboard_table__title{font-size:24px;font-weight:700;color:var(--as-text);margin:0 0 14px}

    /* Notice / Info banner */
    .notice-board{background:#fff8f3;border:1px solid #ffd9bf;border-left:4px solid var(--as-primary) !important;border-radius:10px;padding:14px 18px;margin:0 0 20px}
    .notice-board p{margin:0;color:#7a4828;font-size:14px;line-height:1.5}
    .notice-board p.text-secondary{color:#7a4828 !important}
    .notice-board p.text-danger{color:#b91c1c;font-weight:500}

    /* Step indicators (tab navigation) */
    .add-service-wrapper{margin-top:24px !important}
    #add-service-tab{display:flex;flex-wrap:wrap;gap:8px;background:#fafbfd;border:1px solid var(--as-border);border-radius:14px;padding:14px;margin-bottom:0}
    #add-service-tab .nav-link{display:inline-flex !important;align-items:center;gap:10px;background:transparent;border:1px solid transparent;border-radius:10px;padding:10px 16px;color:var(--as-muted);font-weight:600;font-size:14px;transition:all .2s;text-decoration:none !important;cursor:pointer;position:relative}
    #add-service-tab .nav-link::before,
    #add-service-tab .nav-link::after{display:none !important;content:none !important;background:none !important}
    #add-service-tab .nav-link:hover{background:#fff;color:var(--as-text);border-color:var(--as-border);text-decoration:none !important}
    #add-service-tab .nav-link.active{background:#fff;color:var(--as-primary);border-color:var(--as-primary-light);box-shadow:0 2px 8px rgba(255,107,44,0.12);text-decoration:none !important}
    #add-service-tab .nav-link.active span,
    #add-service-tab .nav-link span{text-decoration:none !important;border:none !important}
    #add-service-tab .nav-link .nav-link-number{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:#fff;border:1.5px solid var(--as-border);color:var(--as-muted);font-size:13px;font-weight:700;flex-shrink:0;transition:all .2s}
    #add-service-tab .nav-link.active .nav-link-number{background:var(--as-primary);color:#fff;border-color:var(--as-primary)}
    #add-service-tab .nav-link:hover .nav-link-number{border-color:var(--as-primary);color:var(--as-primary)}

    /* Content area */
    .add-service-content-wrapper{margin-top:24px !important}
    .add-service-content,
    .add-service-attribute-content{background:#fff;border:1px solid var(--as-border);border-radius:14px;padding:24px}
    /* Pane height fits content - prevents huge empty card on category/availability steps */
    .add-service-content > .tab-pane{min-height:0 !important}
    .add-service-content > .tab-pane.step{min-height:auto !important;height:auto !important}
    .add-service-content > .tab-pane:not(.show){display:none}
    #service-category,
    #service-media-uploads,
    #service-set-availability{min-height:auto !important;height:auto !important}

    /* Form labels */
    .info-title,
    .label_title{font-size:13px;font-weight:600;color:var(--as-text);margin-bottom:6px;display:inline-block}
    .info-title .text-danger,
    .label_title .text-danger{color:var(--as-danger)}
    .info-title small{font-size:11px;font-weight:400;color:var(--as-muted);margin-left:4px}
    .info-title small.text-info{color:var(--as-info) !important}

    /* Form inputs */
    .form--control{width:100%;background:#fff;border:1px solid var(--as-border);border-radius:10px;padding:11px 14px;font-size:14px;color:var(--as-text);transition:all .15s;outline:none;box-shadow:none}
    .form--control:focus{border-color:var(--as-primary);box-shadow:0 0 0 3px rgba(255,107,44,0.12)}
    .form--control::placeholder{color:#a0aec0}
    .textarea-input{min-height:90px;resize:vertical;line-height:1.5}

    /* Select2 override */
    .select2-container--default .select2-selection--single{height:42px !important;border:1px solid var(--as-border) !important;border-radius:10px !important;padding:4px 10px}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:32px !important;color:var(--as-text)}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:40px !important}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{border-color:var(--as-primary) !important;box-shadow:0 0 0 3px rgba(255,107,44,0.12)}
    .select2-dropdown{border:1px solid var(--as-border) !important;border-radius:10px !important;box-shadow:0 8px 24px rgba(0,0,0,0.08)}
    .select2-results__option--highlighted{background:var(--as-primary) !important}
    select.subcategory,
    #subcategory,
    #child_category{width:100%;background:#fff;border:1px solid var(--as-border);border-radius:10px;padding:11px 14px;font-size:14px;color:var(--as-text);min-height:42px;outline:none;transition:all .15s}
    select.subcategory:focus,
    #subcategory:focus,
    #child_category:focus{border-color:var(--as-primary);box-shadow:0 0 0 3px rgba(255,107,44,0.12)}

    /* Summernote editor */
    .note-editor.note-frame{border:1px solid var(--as-border) !important;border-radius:10px !important;overflow:hidden}
    .note-editor.note-frame .note-toolbar{background:#fafbfd !important;border-bottom:1px solid var(--as-border) !important;padding:8px 12px !important}
    .note-editor.note-frame .note-statusbar{background:#fafbfd !important;border-top:1px solid var(--as-border) !important}
    .note-editor.note-frame .note-editing-area .note-editable{padding:14px 16px !important;font-size:14px;color:var(--as-text)}
    .note-btn{background:#fff !important;border:1px solid var(--as-border) !important;color:var(--as-text) !important;border-radius:6px !important;margin:2px !important}
    .note-btn:hover{background:var(--as-primary-light) !important;color:var(--as-primary) !important}
    .note-btn-group .active{background:var(--as-primary) !important;color:#fff !important}

    /* Permalink display */
    .permalink_label{background:#fafbfd;border:1px dashed var(--as-border);border-radius:8px;padding:10px 12px;margin-top:8px;font-size:13px}
    #slug_show{color:var(--as-primary) !important;font-family:monospace;word-break:break-all}

    /* Buttons */
    .btn{padding:9px 18px;border-radius:10px;font-weight:600;font-size:13px;border:1px solid transparent;transition:all .15s;cursor:pointer;display:inline-flex;align-items:center;gap:6px}
    .btn-sm{padding:6px 12px;font-size:12px}
    .btn-warning{background:var(--as-warning);color:#fff;border-color:var(--as-warning)}
    .btn-warning:hover{background:#d97706;border-color:#d97706;color:#fff}
    .btn-info{background:var(--as-info);color:#fff;border-color:var(--as-info)}
    .btn-info:hover{background:#0284c7;border-color:#0284c7;color:#fff}
    .btn-success{background:var(--as-success);color:#fff;border-color:var(--as-success)}
    .btn-success:hover{background:#059669;border-color:#059669;color:#fff}
    .btn-danger{background:var(--as-danger);color:#fff;border-color:var(--as-danger)}
    .btn-danger:hover{background:#dc2626;border-color:#dc2626;color:#fff}
    .btn-secondary{background:#64748b;color:#fff;border-color:#64748b}

    /* Add More button */
    .btn-see-more.style-02{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;background:var(--as-primary-light);border:1px dashed var(--as-primary);border-radius:10px;color:var(--as-primary);font-weight:600;font-size:13px;text-decoration:none;transition:all .15s;cursor:pointer}
    .btn-see-more.style-02:hover{background:var(--as-primary);color:#fff;border-style:solid;transform:translateY(-1px)}
    .btn-see-more.style-02::before{content:"+";font-size:18px;line-height:1}

    /* Previous / Next / Publish action buttons */
    #prevBtn,#nextBtn,#submitBtn{padding:11px 26px;border-radius:10px;font-weight:700;font-size:14px;transition:all .15s}
    #prevBtn{background:#fff;border:1.5px solid var(--as-border);color:var(--as-text)}
    #prevBtn:hover{background:#fafbfd;border-color:var(--as-muted)}
    #nextBtn{background:var(--as-primary);border:none;color:#fff}
    #nextBtn:hover{background:var(--as-primary-dark);transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,107,44,0.25)}
    #submitBtn{background:var(--as-success);border:none;color:#fff;font-weight:700}
    #submitBtn:hover{background:#059669;transform:translateY(-1px);box-shadow:0 4px 12px rgba(16,185,129,0.25)}

    /* Repeating rows (includes, additional services, faqs) */
    .what-include-element,
    .additional-services .row.g-4,
    .single-dashboard-input.faqs{background:#fafbfd;border:1px solid var(--as-border);border-radius:12px;padding:16px;margin-bottom:14px;position:relative}
    .what-include-element + .what-include-element{margin-top:0}

    /* Service attribute sub-tabs (vertical on left) */
    #add-service-attribute-tab{background:#fafbfd;border:1px solid var(--as-border);border-radius:14px;padding:10px;gap:6px}
    #add-service-attribute-tab .nav-link{display:flex;align-items:center;gap:10px;padding:11px 14px;color:var(--as-muted);font-weight:600;font-size:13px;border-radius:8px;transition:all .15s;background:transparent;border:1px solid transparent;text-decoration:none}
    #add-service-attribute-tab .nav-link:hover{background:#fff;color:var(--as-text)}
    #add-service-attribute-tab .nav-link.active{background:#fff;color:var(--as-primary);border-color:var(--as-primary-light);box-shadow:0 1px 4px rgba(0,0,0,0.04)}
    #add-service-attribute-tab .nav-link .nav-link-number{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#fff;border:1.5px solid var(--as-border);color:var(--as-muted);font-size:11px;font-weight:700;flex-shrink:0}
    #add-service-attribute-tab .nav-link.active .nav-link-number{background:var(--as-primary);color:#fff;border-color:var(--as-primary)}

    /* Service price card on the side */
    .edit-service-wrappers{background:#fff;border:1px solid var(--as-border);border-radius:12px;padding:16px;margin-top:14px !important}
    .edit-service-wrappers .info-title{color:var(--as-primary);font-weight:700}
    .edit-service-wrappers .form--control{background:#fafbfd;font-size:16px;font-weight:700;color:var(--as-text)}

    /* Section headings inside content */
    .input-title{font-size:16px;font-weight:700;color:var(--as-text);margin:0 0 6px}

    /* Service Online toggle (Step 3) */
    .online_service .dashboard-switch-single{align-items:center;gap:12px;background:#fafbfd;border:1px solid var(--as-border);border-radius:10px;padding:10px 16px;display:inline-flex !important}
    .online_service .text-info{color:var(--as-text) !important;font-weight:600;font-size:13px}

    /* Custom switch — hide checkbox, label = visible pill */
    input.custom-switch[type="checkbox"]{position:absolute !important;opacity:0 !important;pointer-events:none !important;width:0 !important;height:0 !important;margin:0 !important;display:block !important}
    .switch-label{position:relative !important;display:inline-block !important;width:46px !important;height:24px !important;background:#cbd5e0 !important;border-radius:12px !important;cursor:pointer !important;transition:background .2s !important;margin:0 !important;flex-shrink:0 !important;vertical-align:middle;overflow:hidden}
    /* Kill any pre-existing ::after thumb from project CSS */
    .switch-label::after{content:none !important;display:none !important;background:none !important;width:0 !important;height:0 !important}
    .switch-label::before{content:"" !important;position:absolute !important;top:2px !important;left:2px !important;width:20px !important;height:20px !important;background:#fff !important;border-radius:50% !important;transition:transform .2s !important;box-shadow:0 1px 4px rgba(0,0,0,0.25) !important;display:block !important}
    input.custom-switch[type="checkbox"]:checked + .switch-label{background:var(--as-primary) !important}
    input.custom-switch[type="checkbox"]:checked + .switch-label::before{transform:translateX(22px) !important}

    /* Available all cities toggle (Step 5) */
    .available-all-city-area{display:flex;align-items:center;gap:14px;background:#fafbfd;border:1px solid var(--as-border);border-radius:12px;padding:14px 18px;max-width:fit-content}
    .available-all-city-area .text-info{color:var(--as-text) !important;font-weight:600;font-size:14px}
    .available-all-city-area .dashboard-switch-single{display:inline-flex !important;align-items:center;gap:0}

    /* Media upload buttons */
    .media-upload-btn-wrapper{background:#fafbfd;border:2px dashed var(--as-border);border-radius:12px;padding:24px;text-align:center;transition:all .15s}
    .media-upload-btn-wrapper:hover{border-color:var(--as-primary);background:#fff8f3}
    .media-upload-btn-wrapper .btn-info{margin-top:8px;margin-bottom:8px}
    .media-upload-btn-wrapper small{display:inline-block;color:var(--as-muted);font-size:11px;margin-top:6px}
    .media-upload-btn-wrapper .img-wrap{margin-bottom:10px}
    .media-upload-btn-wrapper .img-wrap:not(:empty){background:#fff;border-radius:8px;padding:8px}

    /* Modal redesign */
    .modal-content{border:none;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.15)}
    .modal-header{border-bottom:1px solid var(--as-border);padding:16px 22px;background:#fafbfd}
    .modal-title{font-size:16px;font-weight:700;color:var(--as-text)}
    .modal-body{padding:22px}
    .modal-footer{border-top:1px solid var(--as-border);padding:14px 22px;gap:8px}

    /* Tag inputs */
    .meta-content .bootstrap-tagsinput{width:100%;background:#fff;border:1px solid var(--as-border) !important;border-radius:10px !important;padding:8px !important;min-height:42px}
    .meta-content .bootstrap-tagsinput .tag{margin-right:4px !important;background:var(--as-primary-light) !important;color:var(--as-primary) !important;font-size:13px !important;line-height:24px !important;padding:3px 10px !important;border-radius:6px !important;border:none !important}

    /* Error message wrapper */
    .alert-danger{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--as-danger);color:#991b1b;border-radius:10px;padding:12px 16px;font-size:14px}

    /* Form row spacing */
    .single-dashboard-input{margin-bottom:14px}
    .single-dashboard-input:last-child{margin-bottom:0}
    .single-info-input{margin-bottom:0}

    /* Remove button on repeating rows - smaller, top-right */
    .what-include-element .btn-danger,
    .additional-services .btn-danger,
    .single-dashboard-input.faqs ~ .col-xl-2 .btn-danger,
    .row .btn-danger.remove-faqs{width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;font-size:14px}

    /* Section dividers */
    .single-settings{padding-top:8px}
    .single-settings + .single-settings,
    .single-settings.margin-top-40{margin-top:24px !important;padding-top:24px;border-top:1px solid var(--as-border)}

    /* AI Generate button */
    .btn-success.mb-2[data-bs-target="#promptModal"]{background:linear-gradient(135deg,#8b5cf6 0%,#7c3aed 100%);border:none;font-weight:600;padding:8px 16px;font-size:13px;box-shadow:0 2px 8px rgba(139,92,246,0.25)}
    .btn-success.mb-2[data-bs-target="#promptModal"]:hover{background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%);transform:translateY(-1px);box-shadow:0 4px 12px rgba(139,92,246,0.35)}
    .btn-success.mb-2[data-bs-target="#promptModal"]::before{content:"✨";margin-right:2px}

    /* Mobile responsive */
    @media(max-width:768px){
        .dashboard__inner__item{padding:18px}
        .dashboard_table__title{font-size:20px}
        #add-service-tab{flex-direction:row;overflow-x:auto;padding:10px;gap:6px;-webkit-overflow-scrolling:touch}
        #add-service-tab::-webkit-scrollbar{display:none}
        #add-service-tab .nav-link{padding:8px 12px;font-size:12px;white-space:nowrap;flex-shrink:0}
        #add-service-tab .nav-link .nav-link-number{width:22px;height:22px;font-size:11px}
        .add-service-content,
        .add-service-attribute-content{padding:16px}
        .what-include-element,
        .additional-services .row.g-4,
        .single-dashboard-input.faqs{padding:12px}
        #prevBtn,#nextBtn,#submitBtn{padding:10px 18px;font-size:13px;flex:1;justify-content:center}
    }
</style>
