<!-- Global Platform Error / Alert Notification Modal -->
<div id="platformErrorModal" 
     class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-stone-950/70 backdrop-blur-md animate-backdrop-fade"
     role="dialog" 
     aria-modal="true"
     aria-labelledby="platformErrorModalTitle">
    
    <div class="animate-modal-pop-up bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 rounded-3xl p-5 sm:p-7 max-w-md w-full shadow-2xl relative text-center space-y-4">
        
        <!-- Icon -->
        <div id="platformErrorModalIconWrapper" class="w-14 h-14 rounded-2xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto shadow-sm">
            <i data-lucide="alert-triangle" class="w-7 h-7" id="platformErrorModalIcon"></i>
        </div>

        <!-- Title & Message -->
        <div class="space-y-2">
            <h3 id="platformErrorModalTitle" class="text-lg sm:text-xl font-black text-stone-900 dark:text-stone-100 tracking-tight leading-snug">
                Notice
            </h3>
            <div id="platformErrorModalMessage" class="text-xs sm:text-sm text-stone-600 dark:text-stone-300 leading-relaxed break-words whitespace-pre-line max-h-60 overflow-y-auto px-1">
                An unexpected event occurred.
            </div>
        </div>

        <!-- Action & Dismiss Buttons -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-2">
            <!-- Dynamic Action Button (e.g. 1-click Lock-In) -->
            <button type="button" 
                    id="platformErrorModalActionBtn" 
                    class="hidden w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs sm:text-sm shadow-md transition active:scale-95 items-center justify-center gap-1.5">
                <i data-lucide="zap" class="w-4 h-4"></i>
                <span id="platformErrorModalActionText">Action</span>
            </button>

            <!-- Dismiss Button -->
            <button type="button" 
                    id="platformErrorModalCloseBtn"
                    onclick="window.closePlatformErrorModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200 font-bold text-xs sm:text-sm transition active:scale-95">
                Got It
            </button>
        </div>

        <!-- Close 'X' in corner -->
        <button type="button" 
                onclick="window.closePlatformErrorModal()" 
                class="absolute top-3.5 right-3.5 p-1.5 text-stone-400 hover:text-stone-700 dark:hover:text-stone-200 hover:bg-stone-100 dark:hover:bg-stone-800 rounded-xl transition">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<script>
    (function() {
        let currentActionCallback = null;

        window.showPlatformError = function(message, title = 'Notice', actionConfig = null) {
            const modal = document.getElementById('platformErrorModal');
            if (!modal) {
                console.warn('platformErrorModal not found, falling back to console:', message);
                return;
            }

            const titleEl = document.getElementById('platformErrorModalTitle');
            const msgEl = document.getElementById('platformErrorModalMessage');
            const actionBtn = document.getElementById('platformErrorModalActionBtn');
            const actionText = document.getElementById('platformErrorModalActionText');

            if (titleEl) titleEl.textContent = title || 'Notice';
            if (msgEl) {
                // If message is a string with newlines or sentences
                msgEl.textContent = message || 'An error occurred. Please try again.';
            }

            currentActionCallback = null;
            if (actionConfig && actionConfig.label && (actionConfig.onClick || actionConfig.href)) {
                if (actionBtn && actionText) {
                    actionText.textContent = actionConfig.label;
                    actionBtn.classList.remove('hidden');
                    actionBtn.classList.add('inline-flex');

                    currentActionCallback = function() {
                        if (typeof actionConfig.onClick === 'function') {
                            actionConfig.onClick();
                        } else if (actionConfig.href) {
                            window.location.href = actionConfig.href;
                        }
                        window.closePlatformErrorModal();
                    };
                    actionBtn.onclick = currentActionCallback;
                }
            } else if (actionBtn) {
                actionBtn.classList.add('hidden');
                actionBtn.classList.remove('inline-flex');
                actionBtn.onclick = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        };

        window.closePlatformErrorModal = function() {
            const modal = document.getElementById('platformErrorModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        // Close on backdrop click
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('platformErrorModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        window.closePlatformErrorModal();
                    }
                });
            }
        });

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.closePlatformErrorModal();
            }
        });

        // Modernize global alert() to use our popup modal
        const nativeAlert = window.alert;
        window.alert = function(msg) {
            try {
                window.showPlatformError(String(msg), 'Notice');
            } catch(e) {
                nativeAlert(msg);
            }
        };
    })();
</script>

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showPlatformError(@json(session('error')), 'Notice');
        });
    </script>
@elseif($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allErrors = @json($errors->all());
            window.showPlatformError(allErrors.join('\n'), 'Action Could Not Complete');
        });
    </script>
@endif

