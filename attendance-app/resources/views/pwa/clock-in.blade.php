<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'HadirYuk') }} - Mobile Clock-In</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fafafa; }
        .video-container { position: relative; width: 100%; max-width: 480px; margin: 0 auto; aspect-ratio: 3/4; overflow: hidden; border-radius: 1rem; }
        #webcam { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #output_canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); pointer-events: none; }
    </style>
</head>
<body class="antialiased text-gray-900 h-screen flex flex-col justify-between overflow-hidden">
    
    <!-- Top Nav -->
    <div class="px-5 py-4 bg-white shadow-sm flex items-center justify-between z-10 relative">
        <div class="flex items-center gap-3">
            <a href="{{ route('member.dashboard') }}" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="font-bold text-lg leading-tight">Absen Mandiri</h1>
                <p class="text-xs text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col items-center justify-center p-5 relative">
        
        <!-- Camera Viewport -->
        <div class="video-container shadow-2xl border-4 border-yellow-400 transition-colors duration-500" id="camera-border">
            <video id="webcam" autoplay playsinline></video>
            <canvas id="output_canvas"></canvas>
            
            <!-- Loading Overlay -->
            <div id="camera-loading" class="absolute inset-0 bg-gray-900/80 flex flex-col items-center justify-center text-white z-10">
                <i class="fa-solid fa-spinner fa-spin text-4xl mb-3 text-rose-500"></i>
                <p class="text-sm font-medium">Mengaktifkan Kamera...</p>
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="mt-6 bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 max-w-sm w-full">
            <div id="status-icon" class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-500 flex items-center justify-center text-xl shrink-0 transition-colors">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div>
                <h3 id="status-title" class="font-bold text-gray-800 text-sm">Mencari Wajah</h3>
                <p id="status-desc" class="text-xs text-gray-500 leading-tight">Arahkan wajah Anda ke dalam frame kamera.</p>
            </div>
        </div>
        
    </div>

    <!-- Bottom Actions -->
    <div class="p-5 bg-white border-t border-gray-100 z-10 relative shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <form id="clockin-form" action="{{ route('pwa.store') }}" method="POST">
            @csrf
            <input type="hidden" name="image_snapshot" id="image_snapshot" value="">
            <input type="hidden" name="face_match_score" id="face_match_score" value="">
            <input type="hidden" name="latitude" id="latitude" value="">
            <input type="hidden" name="longitude" id="longitude" value="">
            
            <button type="submit" id="submit-btn" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-4 rounded-xl shadow-sm flex items-center justify-center gap-2 transition-all cursor-not-allowed">
                <i class="fa-regular fa-clock text-xl"></i>
                <span>Clock In Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Script Logic -->
    <script>
        const videoElement = document.getElementById('webcam');
        const canvasElement = document.getElementById('output_canvas');
        const canvasCtx = canvasElement.getContext('2d');
        const submitBtn = document.getElementById('submit-btn');
        const cameraBorder = document.getElementById('camera-border');
        const statusIcon = document.getElementById('status-icon');
        const statusTitle = document.getElementById('status-title');
        const statusDesc = document.getElementById('status-desc');
        const cameraLoading = document.getElementById('camera-loading');
        
        let livenessVerified = false;
        let isGeofencingValid = true; 
        const REQUIRE_GEOFENCING = {{ ($settings->latitude && $settings->longitude) ? 'true' : 'false' }};
        const SETTINGS_LAT = {{ $settings->latitude ?? 'null' }};
        const SETTINGS_LNG = {{ $settings->longitude ?? 'null' }};
        const SETTINGS_RAD = {{ $settings->radius_meters ?? 100 }};

        // Geolocation Logic
        if (REQUIRE_GEOFENCING) {
            isGeofencingValid = false;
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        
                        // Simple Haversine Check (Optional front-end feedback, backend will verify anyway)
                        const R = 6371e3; // metres
                        const φ1 = lat * Math.PI/180;
                        const φ2 = SETTINGS_LAT * Math.PI/180;
                        const Δφ = (SETTINGS_LAT-lat) * Math.PI/180;
                        const Δλ = (SETTINGS_LNG-lng) * Math.PI/180;

                        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                                Math.cos(φ1) * Math.cos(φ2) *
                                Math.sin(Δλ/2) * Math.sin(Δλ/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        const distance = R * c;

                        if (distance <= SETTINGS_RAD) {
                            isGeofencingValid = true;
                            checkUnlock();
                        } else {
                            alert("Anda berada di luar radius sekolah!");
                            statusTitle.innerText = "Lokasi Tidak Sesuai";
                            statusDesc.innerText = `Jarak Anda: ${Math.round(distance)}m (Maks: ${SETTINGS_RAD}m)`;
                        }
                    },
                    (error) => {
                        alert("Gagal mendapatkan lokasi. Harap izinkan akses lokasi.");
                    }
                );
            } else {
                alert("Browser tidak mendukung Geolocation.");
            }
        }

        // Liveness Logic (MediaPipe FaceMesh)
        function updateStatus(phase) {
            if (phase === 1) { // Yellow
                cameraBorder.className = "video-container shadow-2xl border-4 border-yellow-400 transition-colors duration-500";
                statusIcon.className = "w-12 h-12 rounded-full bg-yellow-100 text-yellow-500 flex items-center justify-center text-xl shrink-0 transition-colors";
                statusIcon.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>';
                statusTitle.innerText = "Mencari Wajah";
                statusDesc.innerText = "Arahkan wajah Anda ke dalam frame kamera.";
            } else if (phase === 2) { // Blue
                cameraBorder.className = "video-container shadow-2xl border-4 border-sky-400 transition-colors duration-500";
                statusIcon.className = "w-12 h-12 rounded-full bg-sky-100 text-sky-500 flex items-center justify-center text-xl shrink-0 transition-colors";
                statusIcon.innerHTML = '<i class="fa-regular fa-eye"></i>';
                statusTitle.innerText = "Deteksi Kehidupan";
                statusDesc.innerText = "Silakan kedipkan mata Anda untuk validasi.";
            } else if (phase === 3) { // Green
                cameraBorder.className = "video-container shadow-2xl border-4 border-emerald-400 transition-colors duration-500";
                statusIcon.className = "w-12 h-12 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-xl shrink-0 transition-colors";
                statusIcon.innerHTML = '<i class="fa-solid fa-check"></i>';
                statusTitle.innerText = "Terverifikasi!";
                statusDesc.innerText = "Wajah asli terdeteksi. Silakan Clock In.";
                checkUnlock();
            } else if (phase === 4) { // Purple
                cameraBorder.className = "video-container shadow-2xl border-4 border-purple-400 transition-colors duration-500";
                statusIcon.className = "w-12 h-12 rounded-full bg-purple-100 text-purple-500 flex items-center justify-center text-xl shrink-0 transition-colors";
                statusIcon.innerHTML = '<i class="fa-solid fa-microchip fa-spin"></i>';
                statusTitle.innerText = "Mencocokkan Wajah AI";
                statusDesc.innerText = "Membandingkan dengan Foto Master...";
            } else if (phase === 5) { // Red
                cameraBorder.className = "video-container shadow-2xl border-4 border-rose-500 transition-colors duration-500";
                statusIcon.className = "w-12 h-12 rounded-full bg-rose-100 text-rose-500 flex items-center justify-center text-xl shrink-0 transition-colors";
                statusIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                statusTitle.innerText = "Wajah Tidak Cocok!";
                statusDesc.className = "text-xs text-rose-500 font-bold leading-tight";
                statusDesc.innerText = "Sistem menolak karena wajah berbeda dengan foto profil.";
                livenessVerified = false; // Reset to retry
                setTimeout(() => { updateStatus(1); statusDesc.className = "text-xs text-gray-500 leading-tight"; }, 3000);
            }
        }

        function checkUnlock() {
            if (livenessVerified && isGeofencingValid) {
                submitBtn.disabled = false;
                submitBtn.className = "w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-rose-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer";
            }
        }

        function calculateDistancePoints(p1, p2) {
            return Math.sqrt(Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2));
        }

        const HAS_MASTER = {{ isset($masterPhotoUrl) && $masterPhotoUrl ? 'true' : 'false' }};
        const MASTER_PHOTO_URL = "{!! $masterPhotoUrl ?? '' !!}";
        
        let faceApiLoaded = false;
        let masterDescriptor = null;

        if (HAS_MASTER) {
            Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
                faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
                faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/')
            ]).then(async () => {
                faceApiLoaded = true;
                try {
                    const img = await faceapi.fetchImage(MASTER_PHOTO_URL);
                    const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
                    if (detection) masterDescriptor = detection.descriptor;
                } catch(e) { console.error("Error load master", e); }
            });
        }

        const faceMesh = new FaceMesh({locateFile: (file) => {
            return `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`;
        }});

        faceMesh.setOptions({
            maxNumFaces: 1,
            refineLandmarks: true,
            minDetectionConfidence: 0.5,
            minTrackingConfidence: 0.5
        });

        faceMesh.onResults((results) => {
            if (cameraLoading.style.display !== 'none') {
                cameraLoading.style.display = 'none';
                // Reset canvas dimensions to match video
                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;
            }

            canvasCtx.save();
            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
            // We do not draw the image on canvas so the user just sees the video element normally.
            // We could draw landmarks, but let's keep UI clean.

            if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
                if (!livenessVerified) {
                    const landmarks = results.multiFaceLandmarks[0];
                    
                    const leftV = calculateDistancePoints(landmarks[159], landmarks[145]);
                    const leftH = calculateDistancePoints(landmarks[133], landmarks[33]);
                    const rightV = calculateDistancePoints(landmarks[386], landmarks[374]);
                    const rightH = calculateDistancePoints(landmarks[362], landmarks[263]);
                    
                    const leftEAR = leftV / leftH;
                    const rightEAR = rightV / rightH;
                    const ear = (leftEAR + rightEAR) / 2;
                    
                    if (ear < 0.18) { // Blink detected
                        livenessVerified = true;
                        
                        const snapCanvas = document.createElement('canvas');
                        snapCanvas.width = videoElement.videoWidth;
                        snapCanvas.height = videoElement.videoHeight;
                        const snapCtx = snapCanvas.getContext('2d');
                        snapCtx.translate(snapCanvas.width, 0);
                        snapCtx.scale(-1, 1);
                        snapCtx.drawImage(videoElement, 0, 0);
                        
                        document.getElementById('image_snapshot').value = snapCanvas.toDataURL('image/jpeg', 0.8);
                        
                        if (HAS_MASTER && masterDescriptor && faceApiLoaded) {
                            updateStatus(4); // AI Matching Phase
                            faceapi.detectSingleFace(snapCanvas).withFaceLandmarks().withFaceDescriptor().then(detection => {
                                if (detection) {
                                    const distance = faceapi.euclideanDistance(masterDescriptor, detection.descriptor);
                                    // Convert Euclidean distance to pseudo-similarity percentage where distance 0.45 is ~85%
                                    const similarity = Math.max(0, 100 - (distance * 33.3)); 
                                    document.getElementById('face_match_score').value = similarity.toFixed(2);
                                    
                                    if (distance < 0.45) { // Match success
                                        updateStatus(3);
                                    } else {
                                        updateStatus(5); // Wajah tidak cocok
                                    }
                                } else {
                                    updateStatus(5);
                                }
                            });
                        } else {
                            updateStatus(3); // Langsung sukses jika tidak ada master
                        }
                    } else {
                        updateStatus(2); // Face found, waiting for blink
                    }
                }
            } else {
                if (!livenessVerified) {
                    updateStatus(1);
                }
            }
            canvasCtx.restore();
        });

        const camera = new Camera(videoElement, {
            onFrame: async () => {
                await faceMesh.send({image: videoElement});
            },
            width: 720,
            height: 1280,
            facingMode: "user"
        });
        
        camera.start().catch((err) => {
            alert("Tidak dapat mengakses kamera. Pastikan Anda mengizinkan akses kamera.");
            cameraLoading.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-4xl mb-3 text-red-500"></i><p class="text-sm font-medium">Kamera Diblokir</p>';
        });

        // Form Submit handler
        document.getElementById('clockin-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('submit-btn');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xl"></i><span>Memproses...</span>';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    btn.className = "w-full bg-emerald-500 text-white font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all cursor-not-allowed";
                    btn.innerHTML = '<i class="fa-solid fa-check text-xl"></i><span>' + data.message + '</span>';
                    setTimeout(() => {
                        window.location.href = "{{ route('member.dashboard') }}";
                    }, 1500);
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (err) {
                alert("Terjadi kesalahan sistem. Silakan coba lagi.");
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        });

    </script>
</body>
</html>
