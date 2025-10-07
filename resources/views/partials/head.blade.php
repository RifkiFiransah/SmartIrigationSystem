<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="theme-color" :content="darkMode ? '#0f172a' : '#ffffff'" />
<meta http-equiv="Permissions-Policy"
    content="accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()" />
<title>Irigasi Pintar</title>

<!-- PWA Meta Tags -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Smart Irrigation">
<meta name="application-name" content="Smart Irrigation">
<meta name="msapplication-TileColor" content="#16a34a">
<meta name="msapplication-tap-highlight" content="no">

<!-- Favicon -->
@if (app()->environment('production'))
    <link rel="icon" type="image/png" href="images/agrinexlogo.jpg" />
    <link rel="apple-touch-icon" href="images/agrinexlogo.jpg" />
    <link rel="manifest" href="images/manifest.json">
@else
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('AgrinexLogo.jpg') }}" />
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('AgrinexLogo.jpg') }}" />
@endif

<!-- External Libraries -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {}
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

@include('partials.styles')
