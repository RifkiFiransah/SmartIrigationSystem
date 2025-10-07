{{-- Device Detail Modal --}}
<div x-cloak x-show="showDeviceModal"
    class="fixed inset-0 z-50 modal-overlay flex items-start md:items-center justify-center p-4 md:p-8 bg-black/40 backdrop-blur-sm"
    @keydown.escape.window="closeDeviceModal()" style="z-index: 9999 !important;">
    <div x-show="showDeviceModal" x-transition.opacity x-transition.scale.origin.top
        class="bg-white w-full max-w-3xl rounded-xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col max-h-[92vh] relative"
        style="z-index: 10000 !important;">
        <div class="flex items-start justify-between px-5 py-4 border-b bg-gray-50">
            <div>
                <h3 class="text-lg font-semibold text-gray-800" x-text="selectedDevice?.device_name || 'Device'">
                </h3>
                <p class="text-xs text-gray-500" x-text="selectedDevice ? ('ID: '+selectedDevice.device_id) : ''">
                </p>
            </div>
            <button class="text-gray-500 hover:text-gray-700" @click="closeDeviceModal()">✕</button>
        </div>
        <div class="px-5 pt-5 pb-6 overflow-y-auto space-y-8">
            <!-- Quick stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-[10px] font-semibold text-gray-500">Suhu</div>
                    <div class="font-medium" x-text="fmt(selectedDevice?.temperature_c,'°C')"></div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-gray-500">Tanah</div>
                    <div class="font-medium" x-text="fmt(selectedDevice?.soil_moisture_pct,'%')"></div>
                </div>
                <div>
                    <div class="text-[10px] font-semibold text-gray-500">Baterai</div>
                    <div class="font-medium" x-text="batteryDisplay(selectedDevice)"></div>
                </div>
            </div>

            <!-- Sessions table -->
            <div>
                <h4 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">🚿 Penggunaan Air per
                    Sesi
                    <template x-if="loadingDeviceDetail"><span
                            class="text-xs text-gray-500">(memuat...)</span></template>
                </h4>
                <template x-if="deviceSessionsSummary">
                    <div class="text-[11px] text-gray-600 mb-2">
                        <span x-text="'Total Rencana: ' + fmt(deviceSessionsSummary.total_planned_l,' L')"></span>
                        <span class="mx-2">|</span>
                        <span x-text="'Total Aktual: ' + fmt(deviceSessionsSummary.total_actual_l,' L')"></span>
                        <span class="mx-2">|</span>
                        <span
                            x-text="'Efisiensi: ' + (deviceSessionsSummary.efficiency_pct!=null? deviceSessionsSummary.efficiency_pct+'%':'-')"></span>
                    </div>
                </template>
                <template x-if="!loadingDeviceDetail && !deviceSessions.length">
                    <div class="text-xs text-gray-500">Belum ada data sesi untuk device ini.</div>
                </template>
                <template x-if="deviceSessions.length">
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Sesi</th>
                                    <th class="px-3 py-2 text-left font-medium">Waktu</th>
                                    <th class="px-3 py-2 text-right font-medium">Rencana (L)</th>
                                    <th class="px-3 py-2 text-right font-medium">Aktual (L)</th>
                                    <th class="px-3 py-2 text-right font-medium">Efisiensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="s in deviceSessions" :key="s.id || s.index">
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-3 py-1" x-text="s.index || s.session || '-' "></td>
                                        <td class="px-3 py-1" x-text="s.time || s.start_time || '-' "></td>
                                        <td class="px-3 py-1 text-right"
                                            x-text="s.planned_l ? s.planned_l.toFixed(1) : (s.planned_volume_l?.toFixed(1) || '-')">
                                        </td>
                                        <td class="px-3 py-1 text-right"
                                            x-text="s.actual_l ? s.actual_l.toFixed(1) : (s.actual_volume_l?.toFixed(1) || '-')">
                                        </td>
                                        <td class="px-3 py-1 text-right"
                                            x-text="(s.actual_l && s.planned_l) ? ((s.actual_l / (s.planned_l||1))*100).toFixed(0)+'%' : (s.efficiency_pct ? s.efficiency_pct+'%' : '-')">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>

            <!-- Usage history table -->
            <div>
                <h4 class="text-sm font-semibold text-gray-800 mb-2">📜 Riwayat Penggunaan Air
                    <template x-if="loadingDeviceDetail"><span
                            class="text-xs text-gray-500">(memuat...)</span></template>
                </h4>
                <template x-if="!loadingDeviceDetail && !deviceUsageHistory.length">
                    <div class="text-xs text-gray-500">Belum ada data penggunaan sebelumnya.</div>
                </template>
                <template x-if="deviceUsageHistory.length">
                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Tanggal</th>
                                    <th class="px-3 py-2 text-right font-medium">Total (L)</th>
                                    <th class="px-3 py-2 text-right font-medium">Sesi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="h in deviceUsageHistory" :key="h.date || h.id">
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-3 py-1" x-text="h.date || h.day || '-' "></td>
                                        <td class="px-3 py-1 text-right"
                                            x-text="h.total_l ? h.total_l.toFixed(1) : (h.volume_l?.toFixed(1) || '-')">
                                        </td>
                                        <td class="px-3 py-1 text-right"
                                            x-text="h.sessions || h.session_count || '-' "></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t flex justify-end gap-2">
            <button @click="closeDeviceModal()" class="btn btn-ghost text-xs" x-text="t('close')">Tutup</button>
        </div>
    </div>
</div>
