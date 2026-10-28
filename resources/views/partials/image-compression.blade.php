{{-- resources/views/partials/image-compression.blade.php --}}
<script>
    /**
     * Client-side image compression helper
     */
    async function compressImage(file, { quality = 0.7, maxWidth = 2048, maxHeight = 2048 } = {}) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = event => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(blob => {
                        if (!blob) {
                            reject(new Error('Canvas to Blob failed'));
                            return;
                        }
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        });
                        resolve(compressedFile);
                    }, 'image/jpeg', quality);
                };
                img.onerror = reject;
            };
            reader.onerror = reject;
        });
    }

    /**
     * Intercept form submission and compress large images
     */
    async function handleFormImageCompression(form) {
        const fileInputs = form.querySelectorAll('input[type="file"][accept*="image"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;

        const filesToCompress = [];

        fileInputs.forEach(input => {
            if (input.files.length > 0) {
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    // Compress if larger than 2MB
                    if (file.type.startsWith('image/') && file.size > 2 * 1024 * 1024) {
                        filesToCompress.push({ input, index: i, file });
                    }
                }
            }
        });

        if (filesToCompress.length === 0) return true;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin mr-2"></i> Optimizing Images...';
            if (window.lucide) lucide.createIcons();
        }

        try {
            for (const item of filesToCompress) {
                const compressed = await compressImage(item.file);

                // Replace the file in the input
                const dataTransfer = new DataTransfer();
                const currentFiles = Array.from(item.input.files);
                currentFiles[item.index] = compressed;
                currentFiles.forEach(f => dataTransfer.items.add(f));
                item.input.files = dataTransfer.files;
            }
            return true;
        } catch (error) {
            console.error('Image compression failed:', error);
            return true; // Fallback to original files
        } finally {
            if (submitBtn && originalBtnHtml) {
                // We don't restore the button immediately because the form is about to submit
                // but if something went wrong, we might need to.
            }
        }
    }
</script>