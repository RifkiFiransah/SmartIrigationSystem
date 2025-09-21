@php
    /** @var \App\Filament\Widgets\WeatherOverviewChart $this */
    $badge = $this->currentConditionLabel();
    $rainSimple = $this->rainProbability();
    $rainWeighted = $this->weightedRainProbability();
@endphp
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-3">
                    <span>Analitik Cuaca ({{ $this->hoursWindow }} Jam)</span>
                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded border border-gray-300 bg-white shadow-sm">
                        {{ $badge }}
                    </span>
                </h2>
                <div class="flex items-center gap-3 text-xs text-gray-600">
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] uppercase tracking-wide text-gray-400">Prob Hujan</span>
                        <span class="font-semibold">{{ $rainSimple }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="font-semibold text-indigo-600" title="Weighted (kondisi & recency)">{{ $rainWeighted }}</span>
                    </div>
                    <div class="hidden sm:flex items-center gap-2">
                        <button wire:click="$set('hoursWindow', 12)" class="px-2 py-0.5 rounded text-xs font-medium border {{ $this->hoursWindow == 12 ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50' }}">12h</button>
                        <button wire:click="$set('hoursWindow', 24)" class="px-2 py-0.5 rounded text-xs font-medium border {{ $this->hoursWindow == 24 ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50' }}">24h</button>
                    </div>
                </div>
            </div>
            <!-- Tampilan dipermudah: hanya header dan chart -->
            <div class="relative h-[420px]">
                @php
                    // Tidak boleh pakai 'use' di dalam blok Blade karena dieksekusi dalam method.
                    $color = $this->getColor();
                    $maxHeight = $this->getMaxHeight();
                    $pollingInterval = $this->getPollingInterval();
                @endphp
                <div @if ($pollingInterval) wire:poll.{{ $pollingInterval }}="updateChartData" @endif>
                    <div
                        @if (\Filament\Support\Facades\FilamentView::hasSpaMode())
                            x-load="visible"
                        @else
                            x-load
                        @endif
                        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                        wire:ignore
                        x-data="chart({
                                    cachedData: @js($this->getCachedData()),
                                    options: @js($this->getOptions()),
                                    type: @js($this->getType()),
                                })"
                        @class([
                            match ($color) {
                                'gray' => null,
                                default => 'fi-color-custom',
                            },
                            is_string($color) ? "fi-color-{$color}" : null,
                        ])
                        class="h-full"
                    >
                        <canvas x-ref="canvas" @if ($maxHeight) style="max-height: {{ $maxHeight }}" @endif></canvas>

                        <span
                            x-ref="backgroundColorElement"
                            @class([
                                match ($color) {
                                    'gray' => 'text-gray-100 dark:text-gray-800',
                                    default => 'text-custom-50 dark:text-custom-400/10',
                                },
                            ])
                            @style([
                                \Filament\Support\get_color_css_variables(
                                    $color,
                                    shades: [50, 400],
                                    alias: 'widgets::chart-widget.background',
                                ) => $color !== 'gray',
                            ])
                        ></span>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
