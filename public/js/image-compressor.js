/**
 * Client-Side Image Compression using Canvas API & DataTransfer API
 * Reusable and centralized helper for photo/avatar/master_photo uploads (Students, Teachers, Profile, etc.)
 */
(function(window) {
    'use strict';

    /**
     * Kompres file gambar dari input file dan gantikan file aslinya dengan hasil kompresi.
     * @param {Event|HTMLInputElement} eventOrInput - Event change atau elemen input type="file"
     * @param {Function} callback - Callback opsional yang menerima (previewUrl, compressedFile, originalFile)
     * @param {Object} options - Konfigurasi opsional (maxDim: 800, quality: 0.8, type: 'image/webp')
     */
    window.compressFileInput = function(eventOrInput, callback = null, options = {}) {
        const input = eventOrInput.target || eventOrInput;
        if (!input || !input.files || input.files.length === 0) {
            if (typeof callback === 'function') callback(null, null, null);
            return;
        }

        const file = input.files[0];
        // Jika bukan file gambar, abaikan kompresi dan kembalikan URL objek biasa
        if (!file.type.startsWith('image/')) {
            const fallbackUrl = URL.createObjectURL(file);
            if (typeof callback === 'function') callback(fallbackUrl, file, file);
            return;
        }

        const maxDim = options.maxDim || 800;
        const quality = options.quality !== undefined ? options.quality : 0.8;
        const mimeType = options.type || 'image/webp';

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let width = img.width;
                let height = img.height;

                // Mempertahankan aspek rasio (aspect ratio) jika melebihi batas maxWidth / maxHeight
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                // Proses kompresi via Canvas API
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');

                // Draw image on canvas
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    if (!blob) {
                        console.error('Gagal mengonversi canvas ke Blob.');
                        if (typeof callback === 'function') callback(URL.createObjectURL(file), file, file);
                        return;
                    }

                    // Tentukan nama file baru dengan ekstensi sesuai mimeType (.webp atau .jpg)
                    let extension = '.webp';
                    if (mimeType === 'image/jpeg' || mimeType === 'image/jpg') extension = '.jpg';
                    else if (mimeType === 'image/png') extension = '.png';
                    
                    const baseName = file.name.replace(/\.[^/.]+$/, "");
                    const compressedFileName = baseName + '_compressed' + extension;

                    const compressedFile = new File([blob], compressedFileName, {
                        type: mimeType,
                        lastModified: Date.now()
                    });

                    // Update File Input menggunakan DataTransfer API agar saat submit backend menerima file ringan
                    try {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;
                        console.log(`[Image Compression] Berhasil mengompresi ${file.name}: ${(file.size/1024).toFixed(1)}KB -> ${(compressedFile.size/1024).toFixed(1)}KB (${mimeType} @ quality ${quality*100}%)`);
                    } catch (err) {
                        console.warn('Browser tidak mendukung DataTransfer untuk input.files:', err);
                    }

                    // Buat preview URL baru dari Blob/File terkompresi
                    const previewUrl = URL.createObjectURL(compressedFile);

                    // Panggil callback update UI preview
                    if (typeof callback === 'function') {
                        callback(previewUrl, compressedFile, file);
                    }

                    // Dispatch custom event agar framework (seperti Alpine/Livewire) dapat bereaksi
                    input.dispatchEvent(new CustomEvent('image-compressed', {
                        detail: { previewUrl, compressedFile, originalFile: file },
                        bubbles: true
                    }));
                }, mimeType, quality);
            };
            img.onerror = function() {
                console.error('Gagal memuat gambar untuk kompresi.');
                if (typeof callback === 'function') callback(URL.createObjectURL(file), file, file);
            };
            img.src = e.target.result;
        };
        reader.onerror = function() {
            console.error('Gagal membaca file gambar dengan FileReader.');
            if (typeof callback === 'function') callback(null, null, null);
        };
        reader.readAsDataURL(file);
    };

    /**
     * Auto-intercept helper untuk memasangkan kompresi pada input photo secara otomatis/mudah.
     */
    window.attachImageCompression = function(selector, previewCallback, options = {}) {
        const elements = typeof selector === 'string' ? document.querySelectorAll(selector) : [selector];
        elements.forEach(el => {
            if (el && !el.dataset.compressionAttached) {
                el.dataset.compressionAttached = 'true';
                el.addEventListener('change', (e) => window.compressFileInput(e, previewCallback, options));
            }
        });
    };

})(window);
