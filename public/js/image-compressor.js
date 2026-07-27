/**
 * Client-Side Universal JS Canvas Image Compression
 * Compress photo/avatar/master_photo uploads to image/jpeg @ 0.75 quality (< 200KB) and max 800px dimension.
 * Reusable and centralized helper using Canvas API & DataTransfer API.
 */
(function(window) {
    'use strict';

    /**
     * Core compression function
     * @param {Event|HTMLInputElement} eventOrInput - Event change atau elemen input type="file"
     * @param {Function} callback - Callback opsional yang menerima (previewUrl, compressedFile, originalFile)
     * @param {Object} options - Konfigurasi opsional (maxDim: 800, quality: 0.75, type: 'image/jpeg')
     */
    window.compressFileInput = function(eventOrInput, callback = null, options = {}) {
        const input = (eventOrInput && eventOrInput.target) ? eventOrInput.target : eventOrInput;
        if (!input || !input.files || input.files.length === 0) {
            if (typeof callback === 'function') callback(null, null, null);
            return;
        }

        const file = input.files[0];

        // Skip non-image files or if input is currently compressing to avoid recursion
        if (!file || !file.type || !file.type.startsWith('image/') || input.dataset.compressing === 'true') {
            if (typeof callback === 'function' && file) callback(URL.createObjectURL(file), file, file);
            return;
        }

        input.dataset.compressing = 'true';

        const maxDim = options.maxDim || 800;
        const quality = options.quality !== undefined ? options.quality : 0.75;
        const mimeType = options.type || 'image/jpeg';

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                // c. Hitung dimensi baru dengan mempertahankan aspek rasio (maksimal lebar/tinggi 800px)
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                // d. Gambar ulang pada HTML5 Canvas (<canvas>)
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');

                // Isi latar belakang putih untuk PNG transparan jika dikonversi ke JPEG
                if (mimeType === 'image/jpeg' || mimeType === 'image/jpg') {
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, width, height);
                }

                ctx.drawImage(img, 0, 0, width, height);

                // e. Ekspor gambar canvas menjadi Blob (image/jpeg, kualitas 0.75)
                canvas.toBlob(function(blob) {
                    delete input.dataset.compressing;

                    if (!blob) {
                        console.error('Gagal mengonversi canvas ke Blob.');
                        if (typeof callback === 'function') callback(URL.createObjectURL(file), file, file);
                        return;
                    }

                    // f. Konversi Blob menjadi objek File baru
                    const extension = (mimeType === 'image/jpeg' || mimeType === 'image/jpg') ? '.jpg' : '.png';
                    const baseName = file.name.replace(/\.[^/.]+$/, "");
                    const compressedFileName = baseName + '_compressed' + extension;

                    const compressedFile = new File([blob], compressedFileName, {
                        type: mimeType,
                        lastModified: Date.now()
                    });

                    // f. Perbarui properti input.files menggunakan API DataTransfer()
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;
                        console.log(`[Universal Image Compressor] Berhasil kompresi ${file.name} (${(file.size/1024).toFixed(1)}KB) -> ${(compressedFile.size/1024).toFixed(1)}KB (${mimeType} @ ${quality * 100}%)`);
                    } catch (err) {
                        console.warn('Browser DataTransfer API error:', err);
                    }

                    // g. Update preview URL dan elemen UI jika tersedia
                    const previewUrl = URL.createObjectURL(compressedFile);
                    updateNearbyPreview(input, previewUrl, compressedFile);

                    if (typeof callback === 'function') {
                        callback(previewUrl, compressedFile, file);
                    }

                    // Trigger custom event untuk Alpine.js atau listener lain
                    input.dispatchEvent(new CustomEvent('image-compressed', {
                        detail: { previewUrl, compressedFile, originalFile: file },
                        bubbles: true
                    }));
                }, mimeType, quality);
            };

            img.onerror = function() {
                delete input.dataset.compressing;
                console.error('Gagal memuat elemen gambar.');
                if (typeof callback === 'function') callback(URL.createObjectURL(file), file, file);
            };

            img.src = e.target.result;
        };

        reader.onerror = function() {
            delete input.dataset.compressing;
            console.error('Gagal membaca file gambar.');
            if (typeof callback === 'function') callback(null, null, null);
        };

        reader.readAsDataURL(file);
    };

    /**
     * g. Helper untuk memperbarui elemen preview foto atau teks ukuran file di dekat input
     */
    function updateNearbyPreview(input, previewUrl, compressedFile) {
        if (!input) return;

        const parentContainer = input.closest('form, div, .flex, .grid') || document;
        const targetPreviewId = input.getAttribute('data-preview-target');

        let imgPreview = null;
        if (targetPreviewId) {
            imgPreview = document.getElementById(targetPreviewId);
        }

        if (!imgPreview) {
            imgPreview = parentContainer.querySelector('img.avatar-preview, img[data-avatar-preview], img.preview-image');
        }

        if (imgPreview) {
            imgPreview.src = previewUrl;
        }

        const sizeTextEl = parentContainer.querySelector('.file-size-info, [data-file-size-info]');
        if (sizeTextEl) {
            sizeTextEl.textContent = `Ukuran file: ${(compressedFile.size / 1024).toFixed(1)} KB (Terkompresi)`;
        }
    }

    /**
     * 1. Universal Global Event Listener untuk mendengarkan event 'change' pada semua input foto/avatar
     */
    document.addEventListener('change', function(e) {
        const input = e.target;
        if (!input || input.type !== 'file') return;

        const isAvatarInput = input.classList.contains('compress-avatar') ||
                             input.name === 'avatar' ||
                             input.name === 'master_photo' ||
                             input.hasAttribute('data-compress');

        if (isAvatarInput && input.files && input.files.length > 0 && input.dataset.compressing !== 'true') {
            window.compressFileInput(input, null, {
                maxDim: 800,
                quality: 0.75,
                type: 'image/jpeg'
            });
        }
    });

})(window);
