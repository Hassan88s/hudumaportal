<style>
    /* The admin dark theme forces headings/labels to white — keep text dark inside our white boxes */
    /* dark-mode.css uses `h1,h3,h5,h6{color:#f0f0f0 !important}`, so these need !important too */
    .hc-adm .box,.hc-adm .stat{color:#1f2733 !important}
    .hc-adm .box h3,.hc-adm .box h4,.hc-adm .box h5,.hc-adm .box label,.hc-adm .box td,.hc-adm .box strong,.hc-adm .box span:not(.pill){color:#1f2733 !important}
    .hc-adm .box th,.hc-adm .box small,.hc-adm .box .text-muted,.hc-adm .stat small{color:#6b7280 !important}
    .hc-adm .box a{color:#2563eb !important}
    .hc-adm .box input,.hc-adm .box select{color:#1f2733 !important;background:#fff !important}
    .hc-adm .box label{font-weight:600;display:inline-flex;flex-direction:column;gap:4px;margin:0}
    .hc-adm .box{background:#fff;border:1px solid #e6e9ef;border-radius:10px;margin-bottom:18px;overflow:hidden}
    .hc-adm .box .hd{padding:14px 18px;background:#f8f9fb;border-bottom:1px solid #e6e9ef;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
    .hc-adm .box .hd h3{font-size:14px;font-weight:700;margin:0;text-transform:uppercase;letter-spacing:.4px}
    .hc-adm .box .bd{padding:16px 18px}
    .hc-adm .stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px}
    .hc-adm .stat{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px}
    .hc-adm .stat small{color:#8892a0;text-transform:uppercase;font-size:11px;font-weight:600}
    .hc-adm .stat div{font-size:22px;font-weight:800;color:#c2410c}
    .hc-adm table{width:100%;border-collapse:collapse;font-size:13px}
    .hc-adm th{padding:8px 12px;font-size:11px;text-transform:uppercase;color:#8892a0;text-align:left;border-bottom:1px solid #e6e9ef}
    .hc-adm td{padding:9px 12px;border-bottom:1px solid #f2f4f7;vertical-align:middle}
    .hc-adm .grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .hc-adm .form-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .hc-adm .form-row input,.hc-adm .form-row select{padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px}
    .hc-adm .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;background:#f3f4f6}
    .hc-adm .pill.provisional{background:#fef3c7;color:#92400e}.hc-adm .pill.approved{background:#dbeafe;color:#1e40af}
    .hc-adm .pill.paid{background:#dcfce7;color:#166534}.hc-adm .pill.disqualified{background:#fee2e2;color:#991b1b}
    .hc-adm .pill.risk-high{background:#fee2e2;color:#991b1b}.hc-adm .pill.risk-medium{background:#fef3c7;color:#92400e}
    .hc-adm .pill.risk-low{background:#f3f4f6;color:#4b5563}
    .hc-adm .pill[title]{cursor:help;margin:1px 2px 1px 0}
    @media (max-width:1000px){.hc-adm .grid2{grid-template-columns:1fr}.hc-adm .stats{grid-template-columns:repeat(2,1fr)}}
    .hc-adm .hc-nav{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
    .hc-adm .hc-nav a{padding:7px 13px;background:#fff;border:1px solid #e6e9ef;border-radius:8px;font-size:12px;font-weight:600;color:#4b5563 !important;text-decoration:none}
    .hc-adm .hc-nav a:hover{border-color:#c2410c;color:#c2410c !important}
    .hc-adm .hc-nav a.on{background:#c2410c;border-color:#c2410c;color:#fff !important}
</style>
