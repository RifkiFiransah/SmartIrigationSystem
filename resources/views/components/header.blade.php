<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if (app()->environment('production'))
                <img src="images/agrinexlogo.jpg" alt="Logo"
                    class="h-9 w-9 rounded-md object-cover border border-green-200 shadow-sm" loading="lazy">
            @else
                <img src="{{ asset('AgrinexLogo.jpg') }}" alt="Logo"
                    class="h-9 w-9 rounded-md object-cover border border-green-200 shadow-sm" loading="lazy">
            @endif
            <div>
                <h1 class="text-lg font-semibold text-green-700 leading-tight" x-text="t('appTitle')">Irigasi Pintar</h1>
                <p class="text-xs text-gray-600 -mt-0.5" x-text="t('appSubtitle')">Monitoring & otomasi penyiraman</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- PWA Install Button -->
            <button @click="window.testPWAInstall ? testPWAInstall() : console.log('testPWAInstall not loaded')" 
                class="btn btn-ghost text-green-600 hover:bg-green-50 hidden sm:flex items-center gap-1.5"
                title="Test PWA Install (Ctrl+Shift+P)">
                <span class="text-base">📱</span>
                <span class="text-xs font-semibold">PWA</span>
            </button>
            
            <!-- Language Toggle -->
            <button @click="toggleLanguage()" class="btn btn-ghost flex items-center gap-1.5" :title="t('switchLang')">
                <span class="text-base" x-html="currentLang === 'id' ? '🇮🇩' : '🇬🇧'"></span>
                <span class="text-xs font-semibold uppercase" x-text="currentLang === 'id' ? 'ID' : 'EN'"></span>
            </button>
            
            <!-- Refresh Button -->
            <button @click="loadAll(true)" class="btn btn-ghost"
                :class="loadingAll ? 'opacity-60 pointer-events-none' : ''">
                <span x-show="!loadingAll">🔄</span>
                <span x-show="loadingAll" class="animate-spin">⏳</span>
                <span class="hidden sm:inline" x-text="loadingAll ? t('loading') : t('refresh')"></span>
            </button>
            
            @auth
                <a href="/admin" class="btn bg-green-600 hover:bg-green-700 text-white" x-text="t('admin')">Admin</a>
            @else
                <a href="/admin/login" class="btn btn-ghost" x-text="t('login')">Masuk</a>
            @endauth
        </div>
    </div>
</header>
