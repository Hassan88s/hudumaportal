<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background: #f4f4f4;
    }

    .card {
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 400px;
        margin: auto;
        text-decoration: none;
        color: #000;
        display: block;
    }

    .card-img {
        width: 100%;
        height: auto; /* IMPORTANT: true image height */
        display: block;
        border-bottom: 1px solid #eee;
    }

    .card-content {
        padding: 15px;
    }

    .card-title {
        font-size: 20px;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #222;
    }

    .card-description {
        font-size: 14px;
        color: #555;
        line-height: 1.6;
        max-height: 90px;
        overflow: hidden;
    }
</style>
</head>
<body>

@php
$bgMarkup = render_background_image_markup_by_attachment_id($service_details->image);
preg_match('/url\((.*?)\)/', $bgMarkup, $matches);
$image_url = $matches[1] ?? '';
@endphp

<a href="{{ url('/service-list/'.$service_details->slug) }}" target="_blank" class="card">
    <img src="{{ $image_url }}" alt="{{ $service_details->title }}" class="card-img">

    <div class="card-content">
        <h3 class="card-title">{{ $service_details->title }}</h3>
        <p class="card-description">{{ $service_details->description }}</p>
    </div>
</a>

</body>
</html>
