<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    @include('partials.head')
    
    {{-- Dashboard JavaScript (Alpine.js) - Must be loaded before body --}}
    @include('partials.dashboard-scripts')
</head>

<body x-data="dashboard()" x-init="applyPersistedTheme(); init();" class="h-full bg-gray-50 text-gray-800 min-h-full">
    
    {{-- Header Navigation --}}
    @include('components.header')

    <main class="container mx-auto px-4 py-6 space-y-6 max-w-7xl">
        
        {{-- Weather, Time & Date Section --}}
        @include('components.weather-summary')

        {{-- Weekly Tasks & Calendar --}}
        @include('components.weekly-tasks')

        {{-- Environmental Charts (Light, Water, Soil, Temp, Humidity) --}}
        @include('components.environmental-charts')

        {{-- Metrics Gauge Cards --}}
        @include('components.metrics-cards')

        {{-- Devices & Water Tank Section --}}
        @include('components.devices-tank')

        {{-- Water Usage Charts (30 days & 24 hours) --}}
        @include('components.usage-charts')

        {{-- Location Maps (Street View & Leaflet) --}}
        @include('components.location-maps')

    </main>

    <footer class="text-center py-6 text-xs text-gray-500">&copy; {{ date('Y') }} Smart Irrigation</footer>

    {{-- PWA Components (Install Prompt, Update Banner, Offline Indicator) --}}
    @include('components.pwa-components')

    {{-- Modals (Device Detail) --}}
    @include('components.modals')

    {{-- PWA Service Worker Scripts --}}
    @include('partials.pwa-scripts')
</body>

</html>
