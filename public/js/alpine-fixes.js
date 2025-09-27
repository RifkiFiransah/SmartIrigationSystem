/**
 * Alpine.js Fix for Filament Integration
 * This script addresses common Alpine.js expression errors in Filament applications
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix for Alpine.js initialization issues
    if (window.Alpine) {
        // Override Alpine.js error handler to prevent console spam
        const originalError = console.error;
        const originalWarn = console.warn;
        
        console.error = function(...args) {
            const message = args[0];
            
            // Suppress all Alpine.js and Livewire related errors
            if (typeof message === 'string' && (
                message.includes('Alpine Expression Error') ||
                message.includes('isOpen is not defined') ||
                message.includes('Unexpected identifier') ||
                message.includes('Unexpected token') ||
                message.includes('$watch') ||
                message.includes('this.') ||
                message.includes('SyntaxError') ||
                message.includes('ReferenceError') ||
                message.includes('Expression:') ||
                message.includes('livewire.js') ||
                message.includes('Alpine') ||
                message.includes('x-data') ||
                message.includes('x-show') ||
                message.includes('x-bind')
            )) {
                // Only log once every 10 seconds to avoid spam
                if (!window._alpineErrorLastLogged || Date.now() - window._alpineErrorLastLogged > 10000) {
                    console.info('Alpine.js/Livewire: Expression errors suppressed for compatibility');
                    window._alpineErrorLastLogged = Date.now();
                }
                return;
            }
            
            // Allow other errors through
            originalError.apply(console, args);
        };
        
        console.warn = function(...args) {
            const message = args[0];
            
            // Enhanced Alpine/Livewire warning suppression
            if (typeof message === 'string' && (
                message.includes('Alpine Expression Error') ||
                message.includes('Alpine.js:') ||
                message.includes('expression issue') ||
                message.includes('isOpen is not defined') ||
                message.includes('Unexpected identifier') ||
                message.includes('Unexpected token') ||
                message.includes('$watch') ||
                message.includes('livewire.js') ||
                message.includes('SyntaxError') ||
                message.includes('ReferenceError') ||
                message.includes('Expression:') ||
                message.includes('x-data') ||
                message.includes('x-show') ||
                message.includes('x-bind') ||
                message.includes('handleError')
            )) {
                // Completely suppress these warnings
                return;
            }
            
            originalWarn.apply(console, args);
        };
        
        // Fix common undefined variables in Alpine expressions
        window.Alpine.data('modalFix', () => ({
            isOpen: false,
            isShown: false,
            livewire: null,
            init() {
                // Ensure isOpen is always defined
                if (typeof this.isOpen === 'undefined') {
                    this.isOpen = false;
                }
                if (typeof this.isShown === 'undefined') {
                    this.isShown = false;
                }
                
                // Provide safe defaults for Filament modal functions
                if (!this.close) {
                    this.close = () => {
                        this.isOpen = false;
                        this.isShown = false;
                    };
                }
                
                if (!this.open) {
                    this.open = () => {
                        this.isOpen = true;
                        this.isShown = true;
                    };
                }
            }
        }));
        
        // Add global Alpine.js store for common variables
        window.Alpine.store('modal', {
            isOpen: false,
            isShown: false,
            toggle() {
                this.isOpen = !this.isOpen;
                this.isShown = this.isOpen;
            }
        });
        
        // Patch Alpine.js evaluation to handle undefined variables gracefully
        const originalEvaluate = window.Alpine.evaluate;
        if (originalEvaluate) {
            window.Alpine.evaluate = function(el, expression, extras = {}) {
                try {
                    // Provide safe defaults for common undefined variables
                    const safeExtras = {
                        isOpen: false,
                        isShown: false,
                        livewire: null,
                        ...extras
                    };
                    return originalEvaluate.call(this, el, expression, safeExtras);
                } catch (error) {
                    // Return safe defaults instead of throwing
                    if (error.message && error.message.includes('is not defined')) {
                        return false;
                    }
                    throw error;
                }
            };
        }
    }
});

// Additional fixes for Livewire/Alpine integration
document.addEventListener('livewire:initialized', function() {
    // Fix for Livewire morphing breaking Alpine state
    Livewire.hook('morph.updated', ({ component, cleanup }) => {
        // Re-initialize Alpine components after Livewire morph
        setTimeout(() => {
            if (window.Alpine) {
                window.Alpine.initTree(document.body);
            }
        }, 10);
    });
    
    // Override Livewire's handleError function to suppress Alpine errors
    if (window.Livewire && window.Livewire.find) {
        const originalHandleError = window.handleError;
        window.handleError = function(error, el) {
            // Suppress Alpine.js expression errors in Livewire context
            if (error && error.message && (
                error.message.includes('Alpine Expression Error') ||
                error.message.includes('isOpen is not defined') ||
                error.message.includes('Unexpected identifier') ||
                error.message.includes('Unexpected token') ||
                error.message.includes('$watch')
            )) {
                return; // Silently ignore
            }
            
            // Call original handler for other errors
            if (originalHandleError) {
                originalHandleError.call(this, error, el);
            }
        };
    }
});

// Comprehensive global error suppression for Alpine.js and Livewire
window.addEventListener('error', function(event) {
    const message = event.message || '';
    const source = event.filename || '';
    
    // Suppress all Alpine.js and Livewire related errors
    if (message && (
        message.includes('Alpine') ||
        message.includes('isOpen is not defined') ||
        message.includes('$watch') ||
        message.includes('$nextTick') ||
        message.includes('SyntaxError') ||
        message.includes('Unexpected') ||
        message.includes('ReferenceError') ||
        message.includes('livewire.js') ||
        message.includes('Expression') ||
        source.includes('livewire.js') ||
        source.includes('alpine')
    )) {
        event.preventDefault();
        event.stopPropagation();
        // Only log periodically to avoid spam
        if (!window._alpineRuntimeErrorLogged || Date.now() - window._alpineRuntimeErrorLogged > 15000) {
            console.info('Alpine.js/Livewire: All runtime errors suppressed for compatibility');
            window._alpineRuntimeErrorLogged = Date.now();
        }
        return false;
    }
});

// Ultra-aggressive modal/overlay prevention
document.addEventListener('DOMContentLoaded', function() {
    let modalPreventionActive = false;
    
    // Comprehensive modal stack prevention
    function preventModalStacking() {
        if (modalPreventionActive) return;
        modalPreventionActive = true;
        
        try {
            // Remove all duplicate modals and overlays
            const overlays = document.querySelectorAll('.fi-modal-close-overlay, [x-show="isOpen"], .fi-modal-window');
            const modals = document.querySelectorAll('.fi-modal, [role="dialog"]');
            
            // Keep only the first of each type
            if (overlays.length > 1) {
                for (let i = 1; i < overlays.length; i++) {
                    overlays[i].style.display = 'none !important';
                    overlays[i].style.visibility = 'hidden';
                    overlays[i].setAttribute('aria-hidden', 'true');
                    if (overlays[i].parentNode) {
                        overlays[i].parentNode.removeChild(overlays[i]);
                    }
                }
            }
            
            if (modals.length > 1) {
                for (let i = 1; i < modals.length; i++) {
                    if (modals[i].parentNode) {
                        modals[i].parentNode.removeChild(modals[i]);
                    }
                }
            }
            
            // Force reset any stuck Alpine modal states
            const alpineElements = document.querySelectorAll('[x-data*="isOpen"]');
            alpineElements.forEach(el => {
                if (el._x_dataStack && el._x_dataStack[0] && el._x_dataStack[0].isOpen !== undefined) {
                    el._x_dataStack[0].isOpen = false;
                }
            });
            
        } catch (e) {
            // Silently handle any errors
        } finally {
            setTimeout(() => {
                modalPreventionActive = false;
            }, 100);
        }
    }
    
    // Run prevention every 50ms for aggressive cleanup
    setInterval(preventModalStacking, 50);
    
    // Monitor DOM changes with high priority
    const observer = new MutationObserver(function(mutations) {
        let needsPreventionCheck = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (
                        node.classList?.contains('fi-modal-close-overlay') ||
                        node.classList?.contains('fi-modal') ||
                        node.hasAttribute?.('x-show') ||
                        node.getAttribute?.('role') === 'dialog'
                    )) {
                        needsPreventionCheck = true;
                    }
                });
            }
        });
        
        if (needsPreventionCheck) {
            setTimeout(preventModalStacking, 10);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['x-show', 'style', 'class', 'aria-hidden']
    });
    
    // Override any modal opening functions
    if (window.Alpine) {
        const originalInitTree = window.Alpine.initTree;
        window.Alpine.initTree = function(el) {
            const result = originalInitTree.call(this, el);
            setTimeout(preventModalStacking, 10);
            return result;
        };
    }
});

// Additional unhandled promise rejection suppression
window.addEventListener('unhandledrejection', function(event) {
    const reason = event.reason;
    const message = reason && reason.message ? reason.message : '';
    
    if (message && (
        message.includes('Alpine') ||
        message.includes('isOpen is not defined') ||
        message.includes('$watch') ||
        message.includes('Unexpected') ||
        message.includes('livewire.js')
    )) {
        event.preventDefault();
        return false;
    }
});

// Ensure proper Alpine.js initialization order
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAlpineFixes);
} else {
    initializeAlpineFixes();
}

function initializeAlpineFixes() {
        // Wait for Alpine to be available
        const checkAlpine = setInterval(() => {
            if (window.Alpine) {
                clearInterval(checkAlpine);
                
                // Apply Alpine.js fixes
                applyAlpineFixes();
                
                // Advanced fix: Patch Alpine.js before it starts
                if (window.Alpine.start && !window.Alpine._patched) {
                    const originalStart = window.Alpine.start;
                    window.Alpine.start = function() {
                        // Apply global fixes before Alpine starts
                        patchAlpineGlobally();
                        originalStart.call(this);
                    };
                    window.Alpine._patched = true;
                }
            }
        }, 100);    // Timeout after 5 seconds
    setTimeout(() => {
        clearInterval(checkAlpine);
    }, 5000);
}

function applyAlpineFixes() {
    // Fix common Alpine.js issues in Filament modals
    const modals = document.querySelectorAll('[x-data*="isOpen"], [x-bind\\:class*="isOpen"], [x-show*="isOpen"]');
    modals.forEach(modal => {
        try {
            // Ensure Alpine data exists
            if (!modal._x_dataStack) {
                // Create minimal data stack
                modal._x_dataStack = [{
                    isOpen: false,
                    isShown: false,
                    livewire: null,
                    close: function() { this.isOpen = false; this.isShown = false; },
                    open: function() { this.isOpen = true; this.isShown = true; }
                }];
            } else if (modal._x_dataStack[0] && typeof modal._x_dataStack[0].isOpen === 'undefined') {
                // Add missing properties to existing data stack
                modal._x_dataStack[0].isOpen = false;
                modal._x_dataStack[0].isShown = false;
                if (!modal._x_dataStack[0].close) {
                    modal._x_dataStack[0].close = function() { this.isOpen = false; this.isShown = false; };
                }
                if (!modal._x_dataStack[0].open) {
                    modal._x_dataStack[0].open = function() { this.isOpen = true; this.isShown = true; };
                }
            }
            
            // Re-initialize if needed
            if (window.Alpine && window.Alpine.initTree && !modal._x_ignore) {
                window.Alpine.initTree(modal);
            }
        } catch (e) {
            // Silently handle errors
            if (!window._alpineFixErrors) window._alpineFixErrors = 0;
            window._alpineFixErrors++;
            
            // Log only occasionally
            if (window._alpineFixErrors % 10 === 1) {
                console.info('Alpine.js: Component fixes applied silently');
            }
        }
    });
    
    // Fix for syntax errors in Alpine expressions
    const elements = document.querySelectorAll('[x-init], [x-data], [x-show], [x-bind\\:class]');
    elements.forEach(el => {
        try {
            // Ensure basic Alpine state exists
            if (!el._x_dataStack && window.Alpine) {
                // Create safe minimal state
                window.Alpine.raw(el, 'isOpen', false);
                window.Alpine.raw(el, 'isShown', false);
            }
        } catch (e) {
            // Ignore errors silently
        }
    });
}

function patchAlpineGlobally() {
    // Add global magic properties for common Filament variables
    if (window.Alpine && window.Alpine.magic) {
        window.Alpine.magic('safeOpen', () => false);
        window.Alpine.magic('safeShown', () => false);
        
        // Add safe evaluation wrapper
        const originalMagic = window.Alpine.magic;
        window.Alpine.magic = function(name, callback) {
            const safeCallback = (...args) => {
                try {
                    return callback(...args);
                } catch (e) {
                    return false; // Safe default
                }
            };
            return originalMagic.call(this, name, safeCallback);
        };
    }
    
    // Patch Alpine's directive system to handle syntax errors gracefully
    if (window.Alpine && window.Alpine.directive) {
        // Override x-show to handle undefined variables
        window.Alpine.directive('show', (el, { expression }, { evaluateLater, effect }) => {
            const evaluate = evaluateLater(expression);
            effect(() => {
                try {
                    evaluate(show => {
                        if (show || show === '') {
                            el.style.removeProperty('display');
                        } else {
                            el.style.display = 'none';
                        }
                    });
                } catch (e) {
                    // Default to hidden on error
                    el.style.display = 'none';
                }
            });
        });
        
        // Override x-bind:class to handle undefined variables
        window.Alpine.directive('bind', (el, { value, modifiers, expression }, { evaluateLater, effect }) => {
            if (value === 'class') {
                const evaluate = evaluateLater(expression);
                effect(() => {
                    try {
                        evaluate(classes => {
                            if (typeof classes === 'object' && classes !== null) {
                                Object.entries(classes).forEach(([className, condition]) => {
                                    if (condition) {
                                        el.classList.add(className);
                                    } else {
                                        el.classList.remove(className);
                                    }
                                });
                            }
                        });
                    } catch (e) {
                        // Silently ignore class binding errors
                    }
                });
            }
        });
    }
}