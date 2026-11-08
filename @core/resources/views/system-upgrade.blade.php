<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Upgrade</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
               background: #f6f7fb; color: #1b1f33; margin: 0; padding: 32px 16px; }
        .wrap { max-width: 760px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #e3e6f0; border-radius: 12px;
                padding: 24px; box-shadow: 0 6px 24px rgba(20,24,50,.06); }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .sub { color: #6b7280; font-size: 14px; margin: 0 0 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eef0f6; vertical-align: top; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
        .pill { display: inline-block; font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
        .applied { background: rgba(27,143,90,.12); color: #15794c; }
        .skipped { background: rgba(0,0,0,.06); color: #6b7280; }
        .failed  { background: rgba(200,30,50,.12); color: #b3243c; }
        .detail { color: #6b7280; font-size: 13px; }
        .banner { border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 14px; }
        .ok   { background: rgba(27,143,90,.1); color: #15794c; }
        .bad  { background: rgba(200,30,50,.1); color: #b3243c; }
        .foot { color: #6b7280; font-size: 13px; margin-top: 18px; }
        @media (prefers-color-scheme: dark) {
            body { background: #11141f; color: #e7e9f3; }
            .card { background: #1a1e2d; border-color: #2a3047; box-shadow: none; }
            th, td { border-bottom-color: #262c40; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>System Upgrade</h1>
        <p class="sub">Schema and permissions brought up to date with the deployed code.</p>

        @if($failed === 0)
            <div class="banner ok"><strong>Done.</strong> Everything is up to date — this page is safe to reload.</div>
        @else
            <div class="banner bad"><strong>{{ $failed }} step(s) failed.</strong> Nothing else was rolled back; fix the cause and run this again.</div>
        @endif

        <table>
            <thead>
            <tr><th>Step</th><th>Result</th></tr>
            </thead>
            <tbody>
            @foreach($results as $r)
                <tr>
                    <td>
                        {{ $r['title'] }}
                        @if($r['detail'])
                            <div class="detail">{{ $r['detail'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if($r['status'] === 'applied')
                            <span class="pill applied">applied</span>
                        @elseif($r['status'] === 'FAILED')
                            <span class="pill failed">failed</span>
                        @else
                            <span class="pill skipped">already applied</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <p class="foot">Caches cleared: {{ implode(', ', $cleared) ?: 'none' }}</p>
    </div>
</div>
</body>
</html>
