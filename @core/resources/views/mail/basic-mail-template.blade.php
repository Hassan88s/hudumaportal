<!DOCTYPE html>
@php
    $default_lang = get_default_language();
    $brand        = get_static_option('site_color') ?: '#ff6b2c';
    $siteTitle    = get_static_option('site_title') ?: 'Huduma Portal';
    $logoId       = get_static_option('site_logo');
@endphp
<html lang="{{ $default_lang }}" dir="{{ get_user_lang_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $siteTitle . ' ' . __('Mail') }}</title>
    <!--[if mso]><style type="text/css">body,table,td,a{font-family:Arial,Helvetica,sans-serif !important;}</style><![endif]-->
    <style>
        body{margin:0;padding:0;background-color:#eef1f6;-webkit-font-smoothing:antialiased;}
        img{border:0;outline:none;text-decoration:none;}
        img.mail-logo{max-height:46px;width:auto;max-width:210px;display:inline-block;}
        a{color:{{ $brand }};}
        .hp-wrap table{border-collapse:collapse;}
        /* Make any table the message injects look tidy */
        .hp-body table{width:100%;border-collapse:collapse;margin:6px 0 16px;}
        .hp-body table td,.hp-body table th{border:1px solid #e6eaf2;padding:9px 12px;font-size:14px;color:#374151;}
        .hp-body table th{background-color:#111d5c;color:#ffffff;text-align:left;}
        .hp-body table tr:nth-child(even) td{background-color:#f6f8fc;}
        .hp-body p{margin:0 0 14px 0;font-size:15px;line-height:1.7;color:#4b5563;}
        .hp-body h1,.hp-body h2,.hp-body h3{color:#111d5c;line-height:1.35;}
        .hp-body img{max-width:100%;}
        [dir="rtl"] .hp-body{text-align:right;}
        @media only screen and (max-width:620px){
            .hp-card{width:100% !important;}
            .hp-pad{padding-left:22px !important;padding-right:22px !important;}
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eef1f6;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef1f6;">
    <tr>
        <td align="center" style="padding:30px 12px;">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="hp-card" style="width:600px;max-width:600px;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

                {{-- Brand header --}}
                <tr>
                    <td align="center" style="background-color:#111d5c;border-radius:16px 16px 0 0;padding:24px 32px;">
                        @if(!empty($logoId))
                            <a href="{{ url('/') }}" style="text-decoration:none;">
                                {!! render_image_markup_by_attachment_id($logoId, 'mail-logo') !!}
                            </a>
                        @else
                            <a href="{{ url('/') }}" style="text-decoration:none;font-size:21px;font-weight:800;letter-spacing:.4px;color:#ffffff;">{{ $siteTitle }}</a>
                        @endif
                    </td>
                </tr>

                {{-- Accent strip (brand colour) --}}
                <tr><td style="height:4px;background-color:{{ $brand }};line-height:4px;font-size:0;">&nbsp;</td></tr>

                {{-- Message body (whatever the email passes) --}}
                <tr>
                    <td class="hp-body hp-pad" style="background-color:#ffffff;padding:38px 36px 30px 36px;">
                        {!! $data['message'] !!}
                    </td>
                </tr>

                {{-- Optional CTA — only if the email supplies a url --}}
                @if(!empty($data['url']))
                    <tr>
                        <td align="center" style="background-color:#ffffff;padding:0 36px 36px 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius:10px;background-color:{{ $brand }};">
                                        <a href="{{ $data['url'] }}" style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;">{{ $data['btn_text'] ?? __('Open Huduma Portal') }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                {{-- Footer --}}
                <tr>
                    <td align="center" style="background-color:#111d5c;border-radius:0 0 16px 16px;padding:26px 32px;">
                        <div style="font-size:12px;color:#aeb6cf;line-height:1.8;">
                            {!! get_footer_copyright_text() !!}
                        </div>
                        <div style="margin-top:6px;">
                            @include('mail.appfooter')
                        </div>
                    </td>
                </tr>

            </table>

            <div style="max-width:600px;margin:14px auto 0;font-size:11px;color:#9aa3b8;font-family:'Segoe UI',Arial,sans-serif;">
                {{ __('This is an automated email from') }} {{ $siteTitle }}.
            </div>

        </td>
    </tr>
</table>

</body>
</html>
