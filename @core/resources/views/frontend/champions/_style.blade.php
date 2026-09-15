<style>
    .hc-page{background:#fafbfc;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1f2733}
    .hc-page .container{max-width:1180px;margin:0 auto;padding:0 20px}
    .hc-hero{background:linear-gradient(135deg,#1f2733 0%,#2d3748 100%);color:#fff;padding:56px 0 80px;position:relative;overflow:hidden}
    .hc-hero::before{content:'';position:absolute;top:-100px;right:-100px;width:340px;height:340px;background:rgba(255,138,84,.15);border-radius:50%}
    .hc-hero .container{position:relative;z-index:1}
    .hc-hero .kicker{display:inline-block;background:rgba(255,255,255,.14);padding:6px 14px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:14px}
    .hc-hero h1{font-size:42px;font-weight:800;line-height:1.1;margin:0 0 10px;color:#fff}
    .hc-hero h1 span{color:#fbbf24}
    .hc-hero .sub{font-size:16px;color:rgba(255,255,255,.85);margin:0 0 24px;max-width:680px;line-height:1.5}
    .hc-tabs{display:inline-flex;gap:5px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:999px;padding:5px;flex-wrap:wrap}
    .hc-tabs a{padding:8px 18px;font-size:13px;font-weight:700;color:rgba(255,255,255,.85);text-decoration:none;border-radius:999px}
    .hc-tabs a.active{background:#fff;color:#1f2733}
    .hc-body{padding:0 0 60px;margin-top:-44px;position:relative;z-index:2}
    .hc-card{background:#fff;border:1px solid #eef0f3;border-radius:14px;box-shadow:0 4px 14px rgba(0,0,0,.04);padding:20px 22px;margin-bottom:18px}
    .hc-card h3{font-size:15px;font-weight:800;margin:0 0 12px;color:#1f2733;text-transform:uppercase;letter-spacing:.4px}
    .hc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
    .hc-stat{background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:16px 18px}
    .hc-stat .lbl{font-size:11px;color:#8892a0;text-transform:uppercase;letter-spacing:.4px;font-weight:700}
    .hc-stat .val{font-size:26px;font-weight:800;color:#c2410c;margin-top:4px}
    .hc-stat .hint{font-size:12px;color:#6b7280;margin-top:2px}
    .hc-table{width:100%;border-collapse:collapse;font-size:14px}
    .hc-table th{padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;text-align:left;font-weight:700;border-bottom:1px solid #eef0f3}
    .hc-table td{padding:12px 14px;border-bottom:1px solid #f2f4f7;vertical-align:middle}
    .hc-table tr.me td{background:#fff7ed}
    .hc-table .rank{font-weight:800;color:#6b7280}
    .hc-table tr.top5 .rank{color:#c2410c}
    .hc-table .hp{font-weight:800;color:#c2410c}
    .hc-pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:700;background:#f3f4f6;color:#374151}
    .hc-pill.pending{background:#fef3c7;color:#92400e}
    .hc-pill.confirmed{background:#dcfce7;color:#166534}
    .hc-pill.reversed{background:#fee2e2;color:#991b1b}
    .hc-bar{height:10px;background:#f3f4f6;border-radius:999px;overflow:hidden}
    .hc-bar span{display:block;height:100%;background:linear-gradient(90deg,#ff8a54,#ff6b3d)}
    .hc-btn{display:inline-block;background:#ff6b3d;color:#fff;padding:9px 18px;border-radius:10px;font-weight:700;font-size:13px;text-decoration:none}
    .hc-btn:hover{background:#1f2733;color:#fff}
    .hc-btn.ghost{background:#fff;color:#1f2733;border:1px solid #d1d5db}
    .hc-empty{padding:40px 20px;text-align:center;color:#8892a0}
    .hc-scroll{overflow-x:auto}
    @media (max-width:900px){.hc-grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:560px){.hc-hero h1{font-size:30px}.hc-grid{grid-template-columns:1fr}.hc-table th,.hc-table td{padding:9px 8px}}
</style>
