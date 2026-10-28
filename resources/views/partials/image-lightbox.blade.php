{{-- resources/views/partials/image-lightbox.blade.php --}}
<div id="globalImageLightbox" 
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/92 backdrop-blur-md transition-opacity duration-300 select-none"
     role="dialog" 
     aria-modal="true" 
     aria-label="Image Preview">
    
    {{-- Top Controls Bar --}}
    <div class="absolute top-0 left-0 right-0 z-20 flex items-center justify-between px-4 sm:px-6 py-4 bg-gradient-to-b from-black/80 via-black/40 to-transparent">
        {{-- Caption / Info --}}
        <div class="flex items-center gap-3 min-w-0 pr-4">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
            <p id="lightboxCaption" class="text-xs sm:text-sm font-medium text-stone-200 truncate">Image Preview</p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 shrink-0">
            {{-- Zoom Toggle --}}
            <button type="button" 
                    id="lightboxZoomBtn" 
                    onclick="toggleLightboxZoom()" 
                    title="Toggle zoom (100% / Fit)"
                    class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-stone-200 hover:text-white transition flex items-center gap-1.5 text-xs font-semibold backdrop-blur-sm border border-white/10">
                <i data-lucide="zoom-in" id="lightboxZoomIcon" class="w-4 h-4"></i>
                <span class="hidden sm:inline" id="lightboxZoomText">Zoom</span>
            </button>

            {{-- Direct Download --}}
            <a id="lightboxDownloadBtn" 
               href="#" 
               download 
               target="_blank" 
               title="Download original image"
               class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-stone-200 hover:text-white transition flex items-center gap-1.5 text-xs font-semibold backdrop-blur-sm border border-white/10">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Save</span>
            </a>

            {{-- Close Button --}}
            <button type="button" 
                    onclick="closeImageLightbox()" 
                    title="Close (Esc)"
                    class="p-2 sm:p-2.5 rounded-xl bg-white/10 hover:bg-red-500/80 active:scale-95 text-stone-200 hover:text-white transition backdrop-blur-sm border border-white/10">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    {{-- Main Image Area --}}
    <div id="lightboxScrollContainer" 
         class="w-full h-full flex items-center justify-center p-3 sm:p-8 overflow-auto cursor-zoom-out"
         onclick="handleLightboxBackdropClick(event)">
        <img id="lightboxImg" 
             src="" 
             alt="Full resolution preview" 
             class="max-h-[88vh] max-w-[92vw] w-auto h-auto object-contain rounded-lg shadow-2xl transition-transform duration-200 cursor-zoom-in"
             onclick="handleLightboxImageClick(event)">
    </div>

    {{-- Bottom Hint --}}
    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none">
        <span class="px-3 py-1 rounded-full bg-black/60 backdrop-blur-md text-stone-300 text-[11px] font-medium border border-white/10 shadow-lg">
            Click photo to zoom • ESC to exit
        </span>
    </div>
</div>

<script>
    (function() {
        let isZoomed = false;

        window.openImageLightbox = function(imageUrl, caption) {
            if (!imageUrl) return;

            const modal = document.getElementById('globalImageLightbox');
            const img = document.getElementById('lightboxImg');
            const captionEl = document.getElementById('lightboxCaption');
            const downloadBtn = document.getElementById('lightboxDownloadBtn');

            img.src = imageUrl;
            captionEl.textContent = caption || 'Image Preview';
            downloadBtn.href = imageUrl;

            // Reset zoom state
            isZoomed = false;
            applyZoomState();

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            if (window.lucide) {
                lucide.createIcons();
            }
        };

        window.closeImageLightbox = function() {
            const modal = document.getElementById('globalImageLightbox');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            isZoomed = false;
            applyZoomState();
        };

        window.toggleLightboxZoom = function() {
            isZoomed = !isZoomed;
            applyZoomState();
        };

        window.handleLightboxImageClick = function(event) {
            event.stopPropagation();
            toggleLightboxZoom();
        };

        window.handleLightboxBackdropClick = function(event) {
            if (event.target.id === 'lightboxScrollContainer') {
                closeImageLightbox();
            }
        };

        function applyZoomState() {
            const img = document.getElementById('lightboxImg');
            const zoomText = document.getElementById('lightboxZoomText');
            const zoomIcon = document.getElementById('lightboxZoomIcon');
            const container = document.getElementById('lightboxScrollContainer');

            if (!img) return;

            if (isZoomed) {
                img.classList.remove('max-h-[88vh]', 'max-w-[92vw]', 'cursor-zoom-in');
                img.classList.add('max-h-none', 'max-w-none', 'cursor-zoom-out');
                if (zoomText) zoomText.textContent = 'Fit';
                if (container) container.classList.remove('items-center');
            } else {
                img.classList.add('max-h-[88vh]', 'max-w-[92vw]', 'cursor-zoom-in');
                img.classList.remove('max-h-none', 'max-w-none', 'cursor-zoom-out');
                if (zoomText) zoomText.textContent = 'Zoom';
                if (container) container.classList.add('items-center');
            }
        }

        // Close on Escape key press
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('globalImageLightbox');
                if (modal && !modal.classList.contains('hidden')) {
                    closeImageLightbox();
                }
            }
        });
    })();
</script>

