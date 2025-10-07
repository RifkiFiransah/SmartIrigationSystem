<style>
    :root {
        color-scheme: light dark;
    }

    .card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 1.25rem;
        transition: all 0.2s ease;
    }

    .card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .stat-label {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.05em;
        color: #6b7280;
        text-transform: uppercase;
    }

    .stat-value {
        font-size: 1.5rem;
        line-height: 2rem;
        font-weight: bold;
        color: #1f2937;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn-ghost {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-ghost:hover {
        background-color: #e5e7eb;
        border-color: #9ca3af;
    }

    .skeleton {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        background-color: #e5e7eb;
        border-radius: 0.25rem;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    @keyframes popIn {
        0% {
            transform: scale(.8);
            opacity: 0
        }
        100% {
            transform: scale(1);
            opacity: 1
        }
    }

    .animate-pop {
        animation: popIn .35s cubic-bezier(.4, 1.4, .4, 1) both
    }

    .metric-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2;
    }

    .metric-icon--small svg {
        width: 16px;
        height: 16px;
        stroke-width: 2;
    }

    .metric-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .metric-card:hover {
        transform: translateY(-2px);
    }

    .gauge-progress {
        transition: stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @media (max-width: 768px) {
        .metrics-container {
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #d1d5db transparent;
        }

        .metrics-container::-webkit-scrollbar {
            height: 4px;
        }

        .metrics-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .metrics-container::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 2px;
        }
    }

    @keyframes rainDrop {
        0% {
            transform: translateY(-100%);
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            transform: translateY(100%);
            opacity: 0;
        }
    }

    .rain-drop {
        animation: rainDrop 2s infinite linear;
    }

    @keyframes batteryPulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.6;
        }
    }

    .battery-full {
        animation: batteryPulse 1.5s infinite ease-in-out;
    }

    /* Leaflet z-index fixes */
    .leaflet-container {
        z-index: 1 !important;
    }

    .leaflet-control-container {
        z-index: 2 !important;
    }

    .leaflet-popup {
        z-index: 3 !important;
    }

    .modal-overlay {
        z-index: 9999 !important;
    }

    .leaflet-tile-pane {
        z-index: 1 !important;
    }

    .leaflet-overlay-pane {
        z-index: 2 !important;
    }

    .leaflet-marker-pane {
        z-index: 3 !important;
    }

    .leaflet-tooltip-pane {
        z-index: 4 !important;
    }

    .leaflet-shadow-pane {
        z-index: 1 !important;
    }

    #leafletMap .leaflet-container,
    #leafletMapFull .leaflet-container {
        z-index: 1 !important;
        position: relative !important;
    }

    .gauge {
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, .12));
    }

    .gauge-inner {
        font-variant-numeric: tabular-nums;
    }

    .card-gradient-mask {
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, .4), transparent 70%);
    }

    .chart-legend-item {
        transition: all 0.2s ease;
    }

    .chart-legend-item:hover {
        transform: translateX(2px);
    }

    /* PWA Styles */
    [x-cloak] {
        display: none !important;
    }

    body.pwa-installed {
        padding-top: env(safe-area-inset-top);
        padding-bottom: env(safe-area-inset-bottom);
    }

    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (display-mode: standalone) {
        body {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
    }

    .pull-to-refresh {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #16a34a;
        color: white;
        transform: translateY(-100%);
        transition: transform 0.3s ease;
        z-index: 9999;
    }

    .pull-to-refresh.active {
        transform: translateY(0);
    }
</style>
