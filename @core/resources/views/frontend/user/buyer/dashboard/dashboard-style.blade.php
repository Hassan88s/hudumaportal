<style>
    /* ========== Modern Buyer Dashboard ========== */
    :root{
        --d-bg:#f5f7fb;
        --d-card:#ffffff;
        --d-border:#e8eaf2;
        --d-text:#1e293b;
        --d-muted:#94a3b8;
        --d-primary:#ff6b2c;
        --d-primary-dark:#e55621;
        --d-primary-light:#ffe9dd;
        --d-success:#34d399;
        --d-success-light:#ecfdf5;
        --d-warning:#fbbf24;
        --d-warning-light:#fffbeb;
        --d-danger:#f87171;
        --d-danger-light:#fef2f2;
        --d-info:#60a5fa;
        --d-info-light:#eff6ff;
        --d-purple:#a78bfa;
        --d-purple-light:#f5f3ff;
        --d-pink:#f472b6;
        --d-pink-light:#fdf2f8;
    }
    .dashboard__body{background:var(--d-bg)}
    .d-welcome{background:linear-gradient(135deg,#ff8e5f 0%,#ff6b2c 50%,#e55621 100%);color:#fff;border-radius:16px;padding:24px 28px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 4px 20px rgba(255,107,44,0.28)}
    .d-welcome h2{margin:0 0 6px 0;font-size:22px;font-weight:700;color:#fff}
    .d-welcome p{margin:0;font-size:14px;color:#fff;font-weight:500;opacity:1}
    .d-welcome .d-welcome-actions{display:flex;gap:8px;flex-wrap:wrap}
    .d-welcome .d-welcome-actions a{background:rgba(255,255,255,.15);color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;transition:all .2s;border:1px solid rgba(255,255,255,.25);display:inline-flex;align-items:center;gap:6px}
    .d-welcome .d-welcome-actions a:hover{background:rgba(255,255,255,.25);transform:translateY(-1px)}

    /* Referral box */
    .d-referral{background:#fff;border:1px solid var(--d-border);border-radius:14px;padding:18px;margin-bottom:20px}
    .d-referral-title{font-size:14px;font-weight:700;color:var(--d-text);margin-bottom:10px;display:flex;align-items:center;gap:8px}
    .d-referral-title i{color:var(--d-primary)}
    .d-referral .input-group{display:flex;gap:0}
    .d-referral .input-group input{flex:1;border:1px solid var(--d-border);border-right:none;padding:10px 14px;border-radius:10px 0 0 10px;font-size:13px;color:var(--d-text);background:#f9fafb;outline:none}
    .d-referral .input-group button{padding:10px 16px;background:var(--d-primary);border:none;color:#fff;border-radius:0 10px 10px 0;cursor:pointer;font-weight:600}
    .d-referral .input-group button:hover{background:var(--d-primary-dark)}
    .d-share-row{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
    .d-share-row a{width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:15px;transition:transform .15s}
    .d-share-row a:hover{transform:translateY(-2px)}
    .d-share-row .fb{background:#1877f2}
    .d-share-row .wa{background:#25d366}
    .d-share-row .tw{background:#000}
    .d-share-row .li{background:#0a66c2}

    /* Quick action tabs */
    .d-quicknav{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
    .d-quicknav a{display:inline-flex;align-items:center;gap:10px;padding:12px 18px;background:#fff;border:1px solid var(--d-border);border-radius:12px;text-decoration:none;color:var(--d-text);font-weight:600;font-size:14px;transition:all .2s}
    .d-quicknav a i{color:var(--d-muted);font-size:18px}
    .d-quicknav a:hover{border-color:var(--d-primary);color:var(--d-primary);transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,107,44,0.18)}
    .d-quicknav a:hover i{color:var(--d-primary)}
    .d-quicknav a.active{background:var(--d-primary-light);border-color:var(--d-primary);color:var(--d-primary)}
    .d-quicknav a.active i{color:var(--d-primary)}
    @media(max-width:768px){.d-quicknav{flex-wrap:nowrap;overflow-x:auto;padding-bottom:4px}.d-quicknav::-webkit-scrollbar{display:none}.d-quicknav a{white-space:nowrap;flex-shrink:0}}

    /* Stat Cards */
    .d-stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px}
    .d-stat{background:#fff;border:1px solid var(--d-border);border-radius:14px;padding:18px;transition:all .2s;text-decoration:none;display:block;color:var(--d-text);position:relative;overflow:hidden;min-height:104px}
    .d-stat:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.06);text-decoration:none}
    .d-stat-row{position:relative;min-height:68px}
    .d-stat-label{font-size:11px;color:var(--d-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.4px;margin:0 60px 8px 0;line-height:1.35;min-height:32px}
    .d-stat-value{font-size:22px;font-weight:800;color:var(--d-text);margin:0;line-height:1.2}
    .d-stat-ico{position:absolute;top:0;right:0;width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
    .d-stat-ico.primary{background:var(--d-primary-light);color:var(--d-primary)}
    .d-stat-ico.success{background:var(--d-success-light);color:var(--d-success)}
    .d-stat-ico.warning{background:var(--d-warning-light);color:var(--d-warning)}
    .d-stat-ico.danger{background:var(--d-danger-light);color:var(--d-danger)}
    .d-stat-ico.info{background:var(--d-info-light);color:var(--d-info)}
    .d-stat-ico.purple{background:var(--d-purple-light);color:var(--d-purple)}
    .d-stat-ico.pink{background:var(--d-pink-light);color:var(--d-pink)}

    /* Generic Card */
    .d-card{background:#fff;border:1px solid var(--d-border);border-radius:14px;padding:20px;margin-bottom:20px}
    .d-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:10px}
    .d-card-title{font-size:16px;font-weight:700;color:var(--d-text);margin:0;display:flex;align-items:center;gap:8px}
    .d-card-title i{color:var(--d-primary)}
    .d-card-action{font-size:13px;color:var(--d-primary);text-decoration:none;font-weight:600}
    .d-card-action:hover{text-decoration:underline}

    /* Recent Orders */
    .d-orders{display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:14px}
    .d-order{border:1px solid var(--d-border);border-radius:12px;padding:14px;background:#f9fafc;transition:all .2s}
    .d-order:hover{border-color:var(--d-primary);background:#fff;box-shadow:0 4px 14px rgba(0,0,0,0.05)}
    .d-order-top{display:flex;gap:12px;align-items:flex-start}
    .d-order-thumb{width:60px;height:60px;border-radius:10px;overflow:hidden;background:#eef0f5;flex-shrink:0}
    .d-order-thumb img{width:100%;height:100%;object-fit:cover}
    .d-order-body{flex:1;min-width:0}
    .d-order-id{font-size:11px;color:var(--d-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.3px;display:block;text-decoration:none}
    .d-order-id span{color:var(--d-text);font-weight:700}
    .d-order-title{font-size:14px;font-weight:700;color:var(--d-text);margin:4px 0 6px;line-height:1.3}
    .d-order-title a{color:var(--d-text);text-decoration:none}
    .d-order-title a:hover{color:var(--d-primary)}
    .d-order-meta{font-size:12px;color:var(--d-muted);line-height:1.5}
    .d-order-meta a{color:var(--d-text);text-decoration:none;font-weight:600}
    .d-order-meta a:hover{color:var(--d-primary)}
    .d-order-actions{display:flex;flex-direction:column;gap:6px;align-items:flex-end}
    .d-order-actions .d-view{width:32px;height:32px;border-radius:8px;background:var(--d-primary-light);color:var(--d-primary);display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
    .d-order-actions .d-view:hover{background:var(--d-primary);color:#fff}
    .d-order-toggle{background:none;border:none;color:var(--d-muted);cursor:pointer;padding:4px;font-size:14px}
    .d-order-details{display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--d-border);font-size:12px}
    .d-order-details.open{display:block}
    .d-order-row{display:flex;justify-content:space-between;padding:5px 0;color:var(--d-muted)}
    .d-order-row strong{color:var(--d-text);font-weight:600}
    .d-badge{display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px}
    .d-badge.pending{background:var(--d-warning-light);color:#92400e}
    .d-badge.active{background:var(--d-info-light);color:#075985}
    .d-badge.completed{background:var(--d-success-light);color:#065f46}
    .d-badge.delivered{background:var(--d-purple-light);color:#5b21b6}
    .d-badge.cancel{background:var(--d-danger-light);color:#991b1b}

    /* Notifications */
    .d-notif-list{max-height:420px;overflow-y:auto;padding-right:4px}
    .d-notif-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--d-border)}
    .d-notif-item:last-child{border-bottom:none}
    .d-notif-icon{width:36px;height:36px;border-radius:10px;background:var(--d-primary-light);color:var(--d-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px}
    .d-notif-body{flex:1;min-width:0}
    .d-notif-text{font-size:13px;color:var(--d-text);line-height:1.45;margin-bottom:4px}
    .d-notif-text a{color:var(--d-text);text-decoration:none;font-weight:500}
    .d-notif-text a:hover{color:var(--d-primary)}
    .d-notif-time{font-size:11px;color:var(--d-muted)}
    .d-empty{text-align:center;padding:28px 12px;color:var(--d-muted);font-size:13px}
    .d-empty i{font-size:32px;display:block;margin-bottom:6px;color:#cfd3da}

    /* Tickets table */
    .d-tk-table{width:100%;border-collapse:collapse;font-size:13px}
    .d-tk-table th{text-align:left;padding:12px;font-size:11px;color:var(--d-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.3px;border-bottom:2px solid var(--d-border);background:#f9fafc}
    .d-tk-table td{padding:14px 12px;border-bottom:1px solid var(--d-border);color:var(--d-text);vertical-align:middle}
    .d-tk-table tr:last-child td{border-bottom:none}
    .d-tk-table .tk-title{font-weight:700;color:var(--d-text);text-decoration:none;display:block;line-height:1.3}
    .d-tk-table .tk-title:hover{color:var(--d-primary)}
    .d-tk-table .tk-id{font-size:11px;color:var(--d-muted);margin-top:2px;display:block}
    .d-tk-table .tk-view{width:32px;height:32px;border-radius:8px;background:var(--d-primary-light);color:var(--d-primary);display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
    .d-tk-table .tk-view:hover{background:var(--d-primary);color:#fff}
    .d-priority{display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px}
    .d-priority.high{background:var(--d-danger-light);color:#991b1b}
    .d-priority.urgent{background:#fee2e2;color:#991b1b}
    .d-priority.medium{background:var(--d-warning-light);color:#92400e}
    .d-priority.low{background:var(--d-info-light);color:#075985}
    .d-status{display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.3px}
    .d-status.open{background:var(--d-success-light);color:#065f46}
    .d-status.close{background:#e5e7eb;color:#4b5563}

    /* Bootstrap col compatibility */
    .row.dashboard-redesign{margin:0 -10px}
    .row.dashboard-redesign>[class*="col-"]{padding:0 10px}

    @media(max-width:768px){
        .d-welcome{padding:20px;text-align:center;justify-content:center}
        .d-welcome h2{font-size:18px}
        .d-orders{grid-template-columns:1fr}
        .d-stat-value{font-size:18px}
        .d-tk-table{font-size:12px}
        .d-tk-table th,.d-tk-table td{padding:8px}
    }
</style>
