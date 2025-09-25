<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        @foreach(\App\Models\Device::all() as $device)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $device->device_name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $device->device_id }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-300">{{ $device->location }}</p>
                    </div>
                    <div class="flex flex-col items-end space-y-1">
                        <!-- Connection Status Badge -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $device->connection_state === 'online' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ ucfirst($device->connection_state) }}
                        </span>
                        <!-- Valve Status Badge -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $device->valve_state === 'open' 
                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                            Valve: {{ ucfirst($device->valve_state) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- Connection Toggle -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Connection</label>
                        <div class="flex bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                            <button 
                                wire:click="toggleConnection({{ $device->id }}, 'online')"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-colors duration-200
                                    {{ $device->connection_state === 'online' 
                                        ? 'bg-green-500 text-white shadow-sm' 
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                                ON
                            </button>
                            <button 
                                wire:click="toggleConnection({{ $device->id }}, 'offline')"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-colors duration-200
                                    {{ $device->connection_state === 'offline' 
                                        ? 'bg-red-500 text-white shadow-sm' 
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                                OFF
                            </button>
                        </div>
                    </div>

                    <!-- Valve Toggle -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Valve Control</label>
                        <div class="flex bg-gray-200 dark:bg-gray-700 rounded-lg p-1">
                            <button 
                                wire:click="toggleValve({{ $device->id }})"
                                class="px-3 py-1 text-xs font-medium rounded-md transition-colors duration-200
                                    {{ $device->valve_state === 'open' 
                                        ? 'bg-blue-500 text-white shadow-sm' 
                                        : 'bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400' }}">
                                {{ $device->valve_state === 'open' ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    <!-- Status Info -->
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>Active: {{ $device->is_active ? 'Yes' : 'No' }}</span>
                            @if($device->last_seen_at)
                                <span>Last seen: {{ $device->last_seen_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if($device->valve_state_changed_at)
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Valve changed: {{ $device->valve_state_changed_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>