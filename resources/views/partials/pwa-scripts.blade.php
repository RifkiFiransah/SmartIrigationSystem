<script>
// PWA Install Prompt Handler
function pwaInstall() {
    return {
        showInstallPrompt: false,
        deferredPrompt: null,
        
        init() {
            console.log('[PWA] 🎯 Alpine: Initializing install component...');
            
            // Check if already installed
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                console.log('[PWA] App is already installed');
                return;
            }

            // Check if user dismissed before
            if (localStorage.getItem('pwa_install_dismissed')) {
                const dismissedDate = new Date(localStorage.getItem('pwa_install_dismissed'));
                const now = new Date();
                const daysSinceDismiss = (now - dismissedDate) / (1000 * 60 * 60 * 24);
                
                if (daysSinceDismiss < 7) {
                    console.log('[PWA] Install prompt dismissed recently');
                    return;
                }
            }

            // Listen for the beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', (e) => {
                console.log('[PWA] 🎯 Alpine: Install prompt available');
                e.preventDefault();
                this.deferredPrompt = e;
                setTimeout(() => {
                    this.showInstallPrompt = true;
                }, 5000);
            });
            
            window.addEventListener('pwa-installable', (e) => {
                if (e.detail) {
                    e.detail.preventDefault();
                    this.deferredPrompt = e.detail;
                    setTimeout(() => {
                        this.showInstallPrompt = true;
                    }, 5000);
                }
            });

            window.addEventListener('appinstalled', () => {
                this.showInstallPrompt = false;
                this.deferredPrompt = null;
                alert('✅ Aplikasi berhasil diinstall!');
            });
        },

        async installPWA() {
            if (!this.deferredPrompt) {
                console.log('[PWA] No install prompt available');
                return;
            }

            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            console.log(`[PWA] User response: ${outcome}`);

            this.deferredPrompt = null;
            this.showInstallPrompt = false;
        },

        dismissInstall() {
            this.showInstallPrompt = false;
            localStorage.setItem('pwa_install_dismissed', new Date().toISOString());
        }
    };
}

// Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swUrl = '{{ app()->environment("production") ? "/images/sw.js" : "/sw.js" }}';
        
        navigator.serviceWorker.register(swUrl)
            .then((registration) => {
                console.log('[PWA] ✅ Service Worker registered!');

                setInterval(() => {
                    registration.update();
                }, 60 * 60 * 1000);

                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            const updateBanner = document.querySelector('[x-data*="showUpdateBanner"]');
                            if (updateBanner && updateBanner.__x) {
                                updateBanner.__x.$data.showUpdateBanner = true;
                            }
                        }
                    });
                });
            })
            .catch((error) => {
                console.error('[PWA] ❌ Registration failed:', error);
            });
    });
}

// Network Status Monitoring
window.addEventListener('online', () => {
    console.log('[PWA] 🟢 Back online!');
});

window.addEventListener('offline', () => {
    console.log('[PWA] 🔴 Connection lost');
});

// Global beforeinstallprompt listener
let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    deferredInstallPrompt = e;
    window.dispatchEvent(new CustomEvent('pwa-installable', { detail: e }));
});

window.addEventListener('appinstalled', (e) => {
    console.log('[PWA] 🎉 App installed!');
    deferredInstallPrompt = null;
});

// Test PWA Install
window.testPWAInstall = async function() {
    if (deferredInstallPrompt) {
        deferredInstallPrompt.prompt();
        const { outcome } = await deferredInstallPrompt.userChoice;
        console.log('[PWA] User choice:', outcome);
        deferredInstallPrompt = null;
    } else {
        console.warn('[PWA] Install prompt not available');
    }
};

// Keyboard shortcut (Ctrl+Shift+P)
document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.shiftKey && e.key === 'P') {
        testPWAInstall();
        e.preventDefault();
    }
});

// Alpine.js PWA Store
document.addEventListener('alpine:init', () => {
    Alpine.store('pwa', {
        isInstalled: window.matchMedia('(display-mode: standalone)').matches,
        isOnline: navigator.onLine
    });
});

console.log('[PWA] PWA features initialized');
</script>
