<!-- resources/views/show-location.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>View User Location</title>
</head>
<body>


<iframe
    width="100%"
    height="450"
    style="border:0"
    loading="lazy"
    allowfullscreen
    referrerpolicy="no-referrer-when-downgrade"
    src="https://www.google.com/maps?q={{ $user->latitude }},{{ $user->longitude }}&hl=es;z=14&output=embed">
</iframe>

</body>
</html>
