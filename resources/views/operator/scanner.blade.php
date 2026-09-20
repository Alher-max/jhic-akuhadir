<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scanner Presensi — HadirSekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        html, body { min-height: 100%; }
        body { background: #f8fafc; }
        #reader video { border-radius: 1rem; object-fit: cover; }
        #reader__scan_region { min-height: 250px; }
        #reader__dashboard_section_csr button {
            border: 0; border-radius: .75rem; background: #0f766e; color: white;
            padding: .6rem 1rem; font-weight: 700; cursor: pointer;
        }
        #reader__dashboard_section_csr select {
            border: 1px solid #cbd5e1; border-radius: .75rem; padding: .55rem; margin: .5rem;
        }
    </style>
</head>
<body class="text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-5xl flex-col px-4 py-6 sm:px-8">
        <header class="mb-6 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-teal-700">HadirSekolah</p>
                <h1 class="text-2xl font-black sm:text-3xl">Scanner Presensi</h1>
                <p class="mt-1 text-sm text-slate-500">Arahkan QR siswa ke kamera atau gunakan scanner USB.</p>
            </div>
            <a href="{{ route('operator.dashboard') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold shadow-sm hover:bg-slate-50">Dasbor</a>
        </header>

        <section class="grid flex-1 gap-6 lg:grid-cols-[1.15fr_.85fr]">
            <div class="rounded-3xl bg-slate-900 p-3 shadow-xl sm:p-5">
                <div id="reader" class="overflow-hidden rounded-2xl bg-slate-800"></div>
                <div class="mt-4 flex items-center justify-between gap-3 text-sm text-slate-300">
                    <span id="scanner-status" class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Menyiapkan kamera…</span>
                    <select id="camera-select" class="hidden max-w-[12rem] rounded-lg border-0 bg-white/10 px-2 py-2 text-xs font-semibold text-white"></select>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="rounded-2xl bg-teal-50 p-3 text-xl text-teal-700">⌨</div>
                        <div><h2 class="font-extrabold">Scanner USB</h2><p class="text-sm text-slate-500">Scan barcode lalu tekan Enter.</p></div>
                    </div>
                    <input id="usb-input" type="text" autocomplete="off" aria-label="Input scanner USB"
                        class="h-1 w-1 opacity-0" tabindex="0">
                    <button id="focus-usb" type="button" class="w-full rounded-xl bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-200">Aktifkan input scanner</button>
                </div>
                <div id="result-banner" class="hidden rounded-3xl border p-6 shadow-sm" role="status" aria-live="polite">
                    <p id="result-label" class="text-xs font-black uppercase tracking-widest"></p>
                    <p id="result-name" class="mt-2 text-2xl font-black"></p>
                    <p id="result-message" class="mt-1 text-sm"></p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
                    <p class="font-bold text-slate-700">Petunjuk</p>
                    <ul class="mt-2 list-inside list-disc space-y-1"><li>Pastikan kamera memiliki izin akses.</li><li>Pilih kamera dari kontrol di bawah video.</li><li>Jangan tutup halaman selama proses pemindaian.</li></ul>
                </div>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const apiUrl = @json(url('/api/v1/attendance/scan'));
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const usbInput = document.getElementById('usb-input');
            const status = document.getElementById('scanner-status'), cameraSelect = document.getElementById('camera-select');
            const banner = document.getElementById('result-banner');
            let scanner, busy = false, bannerTimer, keyBuffer = '', keyTimer;

            const beep = (success) => {
                try {
                    const context = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = context.createOscillator(), gain = context.createGain();
                    oscillator.frequency.value = success ? 880 : 180; oscillator.type = 'sine';
                    gain.gain.setValueAtTime(.12, context.currentTime);
                    oscillator.connect(gain); gain.connect(context.destination);
                    oscillator.start(); oscillator.stop(context.currentTime + (success ? .14 : .3));
                } catch (_) {}
            };
            const showBanner = (success, name, message) => {
                clearTimeout(bannerTimer);
                banner.className = `rounded-3xl border p-6 shadow-sm ${success ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'}`;
                document.getElementById('result-label').textContent = success ? 'Presensi tercatat' : 'Scan gagal';
                document.getElementById('result-name').textContent = name || (success ? 'Siswa sudah presensi' : 'Tidak dapat memproses scan');
                document.getElementById('result-message').textContent = message || '';
                bannerTimer = setTimeout(() => banner.classList.add('hidden'), 3000);
            };
            const submitCode = async (code) => {
                code = String(code || '').trim();
                if (!code || busy) return;
                busy = true; status.innerHTML = '<span class="h-2 w-2 animate-pulse rounded-full bg-sky-400"></span> Memproses scan…';
                try {
                    const response = await fetch(apiUrl, { method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token}, body: JSON.stringify({code}) });
                    const data = await response.json();
                    const success = response.ok && data.success;
                    beep(success);
                    showBanner(success, data.student?.name || data.attendance?.student_name, data.message);
                    status.innerHTML = `<span class="h-2 w-2 rounded-full ${success ? 'bg-emerald-400' : 'bg-rose-400'}"></span> ${success ? 'Siap untuk scan berikutnya' : 'Coba scan kembali'}`;
                } catch (_) { beep(false); showBanner(false, '', 'Koneksi gagal. Periksa jaringan lalu coba lagi.'); status.textContent = 'Koneksi gagal'; }
                finally { busy = false; usbInput.value = ''; usbInput.focus(); }
            };
            const startCamera = async (cameraId) => {
                if (!scanner) scanner = new Html5Qrcode('reader');
                try {
                    await scanner.start(cameraId, {fps: 10, qrbox: {width: 250, height: 250}}, submitCode, () => {});
                    status.innerHTML = '<span class="h-2 w-2 rounded-full bg-emerald-400"></span> Kamera aktif — siap memindai';
                } catch (_) { status.innerHTML = '<span class="h-2 w-2 rounded-full bg-rose-400"></span> Kamera tidak tersedia. Gunakan scanner USB.'; }
            };
            document.getElementById('focus-usb').addEventListener('click', () => usbInput.focus());
            usbInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); submitCode(usbInput.value); } });
            document.addEventListener('keydown', e => {
                if (e.target === usbInput || ['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
                if (e.key === 'Enter') { if (keyBuffer) submitCode(keyBuffer); keyBuffer = ''; return; }
                if (e.key.length === 1) { keyBuffer += e.key; clearTimeout(keyTimer); keyTimer = setTimeout(() => keyBuffer = '', 100); }
            });
            window.addEventListener('click', e => { if (!e.target.closest('button, a, input, select')) usbInput.focus(); });
            Html5Qrcode.getCameras().then(cameras => {
                if (!cameras.length) throw new Error('Kamera tidak ditemukan');
                cameras.forEach((camera, index) => {
                    const option = document.createElement('option');
                    option.value = camera.id; option.textContent = camera.label || `Kamera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });
                cameraSelect.classList.remove('hidden');
                startCamera(cameras[0].id);
            }).catch(() => { status.innerHTML = '<span class="h-2 w-2 rounded-full bg-rose-400"></span> Kamera tidak tersedia. Gunakan scanner USB.'; });
            cameraSelect.addEventListener('change', async () => {
                if (!scanner || !scanner.isScanning) return;
                await scanner.stop();
                startCamera(cameraSelect.value);
            });
            usbInput.focus();
        })();
    </script>
</body>
</html>
