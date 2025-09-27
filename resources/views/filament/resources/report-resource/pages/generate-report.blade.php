<x-filament-panels::page>
    <script>
        // Global overlay prevention and button protection
        window.reportPageProtection = {
            isProcessing: false,
            clickTimeout: null,
            overlayCount: 0,

            init() {
                this.preventOverlays();
                this.setupClickProtection();
                this.monitorModals();
            },

            preventOverlays() {
                // Aggressive overlay prevention
                setInterval(() => {
                    const overlays = document.querySelectorAll(
                        '.fi-modal-close-overlay, [aria-hidden="true"][x-show], [x-show="isOpen"]');
                    let visibleCount = 0;

                    overlays.forEach((overlay, index) => {
                        const isVisible = overlay.style.display !== 'none' &&
                            !overlay.hasAttribute('aria-hidden') ||
                            overlay.getAttribute('aria-hidden') !== 'true';

                        if (isVisible) {
                            visibleCount++;
                            if (visibleCount > 1) {
                                // Force hide duplicate overlays
                                overlay.style.display = 'none !important';
                                overlay.style.visibility = 'hidden';
                                overlay.setAttribute('aria-hidden', 'true');
                                if (overlay.hasAttribute('x-show')) {
                                    overlay.removeAttribute('x-show');
                                }
                            }
                        }
                    });
                }, 100);
            },

            setupClickProtection() {
                document.addEventListener('click', (e) => {
                    // Protect all form buttons and actions
                    if (e.target.closest('button, [role="button"], .fi-btn')) {
                        if (this.isProcessing) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            e.stopPropagation();
                            return false;
                        }

                        // Set processing state
                        this.isProcessing = true;

                        // Clear any existing timeout
                        if (this.clickTimeout) {
                            clearTimeout(this.clickTimeout);
                        }

                        // Reset after delay
                        this.clickTimeout = setTimeout(() => {
                            this.isProcessing = false;
                        }, 1500);
                    }
                }, true); // Use capture phase
            },

            monitorModals() {
                // Monitor for modal-related elements
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'childList' || mutation.type === 'attributes') {
                            // Check for modal stacking
                            const modals = document.querySelectorAll('.fi-modal, [role="dialog"]');
                            if (modals.length > 1) {
                                // Keep only the first modal
                                for (let i = 1; i < modals.length; i++) {
                                    modals[i].remove();
                                }
                            }

                            // Force close any stuck overlays
                            const stuckOverlays = document.querySelectorAll(
                                '[x-data*="isOpen"][x-data*="false"] [x-show="isOpen"]');
                            stuckOverlays.forEach(overlay => {
                                overlay.style.display = 'none';
                            });
                        }
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['x-show', 'style', 'aria-hidden']
                });
            }
        };

        document.addEventListener('alpine:init', () => {
            // Initialize protection system
            window.reportPageProtection.init();

            // Handle Livewire events
            window.addEventListener('reset-processing-flag', () => {
                setTimeout(() => {
                    @this.call('resetProcessingFlag');
                    window.reportPageProtection.isProcessing = false;
                }, 1000);
            });

            // Override Alpine's modal handling
            if (window.Alpine) {
                window.Alpine.data('safeModal', () => ({
                    isOpen: false,
                    open() {
                        // Prevent multiple modals
                        if (document.querySelectorAll('.fi-modal[x-show="true"]').length > 0) {
                            return;
                        }
                        this.isOpen = true;
                    },
                    close() {
                        this.isOpen = false;
                    }
                }));
            }
        });

        // Prevent form submission spam
        document.addEventListener('submit', function(e) {
            if (window.reportPageProtection.isProcessing) {
                e.preventDefault();
                return false;
            }
        });
    </script>
    <style>
        /* Ultra-strong overlay prevention */
        .processing-lock {
            pointer-events: none !important;
            opacity: 0.7 !important;
        }

        /* Hide duplicate modals and overlays */
        .fi-modal-close-overlay:nth-of-type(n+2),
        [x-show="isOpen"]:nth-of-type(n+2),
        .fi-modal:nth-of-type(n+2),
        [role="dialog"]:nth-of-type(n+2) {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            z-index: -9999 !important;
        }

        /* Prevent button spam */
        [data-prevent-double-click="true"][data-processing="true"] {
            pointer-events: none !important;
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }

        /* Force hide stuck overlays */
        [aria-hidden="true"] {
            display: none !important;
        }
    </style>
    <div class="space-y-6" id="report-container">
        {{-- Form Section --}}
        <form wire:submit.prevent="generate" class="bg-white rounded-lg shadow border">
            <div class="p-6">
                <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3" style="margin-bottom: 20px">
                    {{-- <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Export Ringkasan Custom</h3>
                            <p class="text-xs text-gray-500 mt-1">Pilih periode agregasi data untuk export.</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            <!-- Period Selection Dropdown -->
                            <div>
                                <select wire:model="summaryExportPeriod" 
                                        class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="daily">📅 Harian (Per Hari)</option>
                                    <option value="weekly">📊 Mingguan (Per Minggu)</option>
                                    <option value="monthly">📈 Bulanan (Per Bulan)</option>
                                    <option value="custom">⚙️ Custom Range</option>
                                </select>
                            </div>
                            
                            <!-- Custom Date Range (shown only when custom is selected) -->
                            @if($summaryExportPeriod === 'custom')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" 
                                       wire:model="summaryCustomFromDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Dari">
                                <input type="date" 
                                       wire:model="summaryCustomToDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Sampai">
                            </div>
                            @endif
                            
                            <!-- Export Buttons -->
                            <div class="flex space-x-2">
                                <x-filament::button size="sm" color="success" 
                                                    wire:click="exportSummaryExcel" 
                                                    icon="heroicon-o-document-arrow-down"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);" style="margin-right: 10px">
                                    Excel
                                </x-filament::button>
                                <x-filament::button size="sm" color="danger" 
                                                    wire:click="exportSummaryPdf" 
                                                    icon="heroicon-o-document-text"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);">
                                    PDF
                                </x-filament::button>
                            </div>
                            
                            <!-- Period Info -->
                            <div class="text-xs text-gray-400 mt-2">
                                @if($summaryExportPeriod === 'daily')
                                    💡 Data akan diagregasi per hari dalam periode terpilih
                                @elseif($summaryExportPeriod === 'weekly')
                                    💡 Data akan diagregasi per minggu dalam periode terpilih
                                @elseif($summaryExportPeriod === 'monthly')
                                    💡 Data akan diagregasi per bulan dalam periode terpilih
                                @elseif($summaryExportPeriod === 'custom')
                                    💡 Data akan diagregasi sesuai range tanggal yang dipilih
                                @endif
                            </div>
                        </div>
                    </div> --}}
                    <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Export Perangkat</h3>
                            <p class="text-xs text-gray-500 mt-1">Daftar Perangkat & status.</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            <!-- Period Selection Dropdown -->
                            <div>
                                <select wire:model="devicesExportPeriod" 
                                        class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="all">📋 Semua Data</option>
                                    <option value="active">✅ Hanya Device Aktif</option>
                                    <option value="inactive">❌ Hanya Device Tidak Aktif</option>
                                    <option value="custom">⚙️ Custom Range</option>
                                </select>
                            </div>
                            
                            <!-- Custom Date Range (shown only when custom is selected) -->
                            @if($devicesExportPeriod === 'custom')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" 
                                       wire:model="devicesCustomFromDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Dari">
                                <input type="date" 
                                       wire:model="devicesCustomToDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Sampai">
                            </div>
                            @endif
                            
                            <!-- Export Buttons -->
                            <div class="flex space-x-2">
                                <x-filament::button size="sm" color="success" 
                                                    wire:click="exportDevicesExcel" 
                                                    icon="heroicon-o-document-arrow-down"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);" style="margin-right: 10px">
                                    Excel
                                </x-filament::button>
                                <x-filament::button size="sm" color="danger" 
                                                    wire:click="exportDevicesPdf" 
                                                    icon="heroicon-o-document-text"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);">
                                    PDF
                                </x-filament::button>
                            </div>
                            
                            <!-- Info -->
                            <div class="text-xs text-gray-400 mt-2">
                                @if($devicesExportPeriod === 'all')
                                    📋 Semua perangkat dalam sistem
                                @elseif($devicesExportPeriod === 'active')
                                    ✅ Hanya perangkat yang aktif
                                @elseif($devicesExportPeriod === 'inactive')
                                    ❌ Hanya perangkat yang tidak aktif
                                @elseif($devicesExportPeriod === 'custom')
                                    🔧 Perangkat berdasarkan tanggal registrasi
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Export Tangki Air</h3>
                            <p class="text-xs text-gray-500 mt-1">Data kapasitas & level.</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            <!-- Period Selection Dropdown -->
                            <div>
                                <select wire:model="waterStoragesExportPeriod" 
                                        class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="current">🏗️ Status Saat Ini</option>
                                    <option value="active">✅ Tangki Aktif</option>
                                    <option value="low_level">⚠️ Level Rendah (&lt;30%)</option>
                                    <option value="high_capacity">🔝 Kapasitas Tinggi</option>
                                    <option value="custom">⚙️ Custom Range</option>
                                </select>
                            </div>
                            
                            <!-- Custom Date Range (shown only when custom is selected) -->
                            @if($waterStoragesExportPeriod === 'custom')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" 
                                       wire:model="waterStoragesCustomFromDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Dari">
                                <input type="date" 
                                       wire:model="waterStoragesCustomToDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Sampai">
                            </div>
                            @endif
                            
                            <!-- Export Buttons -->
                            <div class="flex space-x-2">
                                <x-filament::button size="sm" color="success" 
                                                    wire:click="exportWaterStoragesExcel" 
                                                    icon="heroicon-o-document-arrow-down"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);" style="margin-right: 10px">
                                    Excel
                                </x-filament::button>
                                <x-filament::button size="sm" color="danger" 
                                                    wire:click="exportWaterStoragesPdf" 
                                                    icon="heroicon-o-document-text"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);">
                                    PDF
                                </x-filament::button>
                            </div>
                            
                            <!-- Info -->
                            <div class="text-xs text-gray-400 mt-2">
                                @if($waterStoragesExportPeriod === 'current')
                                    🏗️ Status level dan kapasitas saat ini
                                @elseif($waterStoragesExportPeriod === 'active')
                                    ✅ Hanya tangki yang aktif
                                @elseif($waterStoragesExportPeriod === 'low_level')
                                    ⚠️ Tangki dengan level air rendah
                                @elseif($waterStoragesExportPeriod === 'high_capacity')
                                    🔝 Tangki dengan kapasitas tinggi (>500L)
                                @elseif($waterStoragesExportPeriod === 'custom')
                                    🔧 Tangki berdasarkan periode tertentu
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Export Sensor Data</h3>
                            <p class="text-xs text-gray-500 mt-1">Data mentah (maks 50k baris).</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            <!-- Period Selection Dropdown -->
                            <div>
                                <select wire:model="sensorDataExportPeriod" 
                                        class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="today">📅 Hari Ini</option>
                                    <option value="week">📊 Minggu Ini</option>
                                    <option value="month">📈 Bulan Ini</option>
                                    <option value="latest">🔥 1000 Data Terbaru</option>
                                    <option value="custom">⚙️ Custom Range</option>
                                </select>
                            </div>
                            
                            <!-- Custom Date Range (shown only when custom is selected) -->
                            @if($sensorDataExportPeriod === 'custom')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" 
                                       wire:model="sensorDataCustomFromDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Dari">
                                <input type="date" 
                                       wire:model="sensorDataCustomToDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Sampai">
                            </div>
                            @endif
                            
                            <!-- Export Buttons -->
                            <div class="flex space-x-2">
                                <x-filament::button size="sm" color="success" 
                                                    wire:click="exportSensorDataExcel" 
                                                    icon="heroicon-o-document-arrow-down"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);" style="margin-right: 10px">
                                    Excel
                                </x-filament::button>
                                <x-filament::button size="sm" color="danger" 
                                                    wire:click="exportSensorDataPdf" 
                                                    icon="heroicon-o-document-text"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);">
                                    PDF
                                </x-filament::button>
                            </div>
                            
                            <!-- Info -->
                            <div class="text-xs text-gray-400 mt-2">
                                @if($sensorDataExportPeriod === 'today')
                                    � Data sensor hari ini
                                @elseif($sensorDataExportPeriod === 'week')
                                    📊 Data sensor minggu ini
                                @elseif($sensorDataExportPeriod === 'month')
                                    📈 Data sensor bulan ini
                                @elseif($sensorDataExportPeriod === 'latest')
                                    🔥 1000 pembacaan sensor terbaru
                                @elseif($sensorDataExportPeriod === 'custom')
                                    ⚙️ Data sensor periode kustom
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border rounded bg-white shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Export Log Penggunaan Air</h3>
                            <p class="text-xs text-gray-500 mt-1">Log penggunaan air harian.</p>
                        </div>
                        <div class="mt-3 space-y-3">
                            <!-- Period Selection Dropdown -->
                            <div>
                                <select wire:model="waterUsageExportPeriod" 
                                        class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="today">📅 Hari Ini</option>
                                    <option value="week">📊 Minggu Ini</option>
                                    <option value="month">📈 Bulan Ini</option>
                                    <option value="high_usage">🔴 Penggunaan Tinggi (>100L)</option>
                                    <option value="custom">⚙️ Custom Range</option>
                                </select>
                            </div>
                            
                            <!-- Custom Date Range (shown only when custom is selected) -->
                            @if($waterUsageExportPeriod === 'custom')
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" 
                                       wire:model="waterUsageCustomFromDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Dari">
                                <input type="date" 
                                       wire:model="waterUsageCustomToDate"
                                       class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                       placeholder="Sampai">
                            </div>
                            @endif
                            
                            <!-- Export Buttons -->
                            <div class="flex space-x-2">
                                <x-filament::button size="sm" color="success" 
                                                    wire:click="exportWaterUsageLogsExcel" 
                                                    icon="heroicon-o-document-arrow-down"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);" style="margin-right: 10px">
                                    Excel
                                </x-filament::button>
                                <x-filament::button size="sm" color="danger" 
                                                    wire:click="exportWaterUsageLogsPdf" 
                                                    icon="heroicon-o-document-text"
                                                    class="flex-1"
                                                    data-prevent-double-click="true"
                                                    onclick="if(this.dataset.processing=='true') return false; this.dataset.processing='true'; this.style.pointerEvents='none'; setTimeout(() => {this.style.pointerEvents='auto'; this.dataset.processing='false';}, 3000);">
                                    PDF
                                </x-filament::button>
                            </div>
                            
                            <!-- Info -->
                            <div class="text-xs text-gray-400 mt-2">
                                @if($waterUsageExportPeriod === 'today')
                                    📅 Log penggunaan air hari ini
                                @elseif($waterUsageExportPeriod === 'week')
                                    📊 Log penggunaan air minggu ini
                                @elseif($waterUsageExportPeriod === 'month')
                                    📈 Log penggunaan air bulan ini
                                @elseif($waterUsageExportPeriod === 'high_usage')
                                    🔴 Log dengan penggunaan air tinggi
                                @elseif($waterUsageExportPeriod === 'custom')
                                    ⚙️ Log penggunaan periode kustom
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                {{ $this->form }}

                <div class="mt-6 flex items-center justify-center">
                    <x-filament::button type="submit" size="lg" icon="heroicon-o-play-circle" class="px-8">
                        Generate Laporan
                    </x-filament::button>
                </div>
            </div>
        </form>

        {{-- Results Section --}}
        @if ($generated)
            <div class="bg-white rounded-lg shadow border">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Laporan Cepat</h3>

                    @php
                        $hasSensorData = collect($reportData)->where('records_count', '>', 0)->count() > 0;
                        $hasWaterData = collect($reportData)->where('water_usage_log_sum_l', '>', 0)->count() > 0;
                    @endphp

                    @if (!$hasSensorData && $hasWaterData)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-orange-800">
                                        <strong>Info:</strong> Tidak ada data sensor untuk periode ini. Laporan hanya
                                        menampilkan data penggunaan air.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif



                    {{-- Summary Stats --}}
                    @if (!empty($summary))
                        <div class="grid grid-cols-5 md:grid-cols-1 gap-4 mb-20">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ number_format($summary['total_records'] ?? 0) }}</div>
                                <div class="text-sm text-gray-600">Total Records</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ $summary['total_devices'] ?? 0 }}
                                </div>
                                <div class="text-sm text-gray-600">Total Devices</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ isset($summary['avg_ground_temp_c']) ? number_format($summary['avg_ground_temp_c'], 1) : '—' }}°C
                                </div>
                                <div class="text-sm text-gray-600">Avg Temp</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ isset($summary['avg_soil_moisture_pct']) ? number_format($summary['avg_soil_moisture_pct'], 1) : '—' }}%
                                </div>
                                <div class="text-sm text-gray-600">Avg Moisture</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ isset($summary['total_water_usage_log_sum_l']) ? number_format($summary['total_water_usage_log_sum_l'], 1) : '0' }}L
                                </div>
                                <div class="text-sm text-gray-600">Water Usage</div>
                            </div>
                        </div>
                    @endif

                    {{-- Data Preview Table --}}
                    @if (!empty($reportData))
                        <div class="overflow-hidden border border-gray-200 rounded-lg mt-20"
                            style="margin-top: 20px;">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 mt-10">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Device</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Records</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Temp (°C)</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Moisture (%)</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Battery (V)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach (array_slice($reportData, 0, 10) as $row)
                                            <tr
                                                class="hover:bg-gray-50 {{ ($row['records_count'] ?? 0) == 0 ? 'bg-gray-50 opacity-60' : '' }}">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $row['tanggal'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $row['device_name'] }}
                                                    @if (($row['records_count'] ?? 0) == 0)
                                                        <span class="text-xs text-orange-600 ml-1">(no sensor
                                                            data)</span>
                                                    @endif
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ $row['records_count'] ?? 0 }}</td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ $row['ground_temp_avg'] ? number_format($row['ground_temp_avg'], 1) : '—' }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ $row['soil_moisture_avg'] ? number_format($row['soil_moisture_avg'], 1) : '—' }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                                                    {{ $row['battery_voltage_avg'] ? number_format($row['battery_voltage_avg'], 2) : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if (count($reportData) > 10)
                                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                                    <p class="text-sm text-gray-700 text-center">
                                        Menampilkan 10 dari {{ count($reportData) }} baris. Download laporan untuk
                                        melihat semua data.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
