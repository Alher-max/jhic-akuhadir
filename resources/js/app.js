
import './image-compressor.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('digitalClock', () => ({
    timeString: '00:00:00',
    dateString: '',
    isGpsRequired: false,
    gpsError: '',
    lat: null,
    lng: null,
    isLoadingGps: false,
    showCameraModal: false,
    stream: null,
    cameraError: '',
    cameraStatus: 'Menyiapkan Kamera...',
    isFallback: 0,
    activeDay: 1,
    async openCamera() {
        console.log('Membuka kamera...');
        this.showCameraModal = true;
        this.cameraError = '';
        this.cameraStatus = 'Menyiapkan Kamera...';
        await this.$nextTick();
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            this.stream = stream;
            if (this.$refs.videoElement) {
                this.$refs.videoElement.srcObject = stream;
                this.$refs.videoElement.play().catch(() => {});
            }
            this.cameraStatus = 'Posisikan Wajah di Dalam Bingkai';
        } catch (err) {
            this.closeCamera(false);
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                this.cameraError = 'Izin kamera ditolak. Harap aktifkan akses kamera melalui setelan browser Anda.';
            } else if (err.name === 'NotFoundError') {
                this.cameraError = 'Perangkat kamera/webcam tidak ditemukan.';
            } else {
                this.cameraError = 'Gagal mengakses kamera: ' + err.message;
            }
        }
    },
    closeCamera(hideModal = true) {
        if (hideModal) {
            this.showCameraModal = false;
        }
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.$refs.videoElement) {
            this.$refs.videoElement.srcObject = null;
        }
    },
    takePhotoAndSubmit() {
        this.cameraStatus = 'Memproses Absensi...';
        const video = this.$refs.videoElement;
        if (video && video.videoWidth) {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.75);
            this.$refs.imageDataInput.value = dataUrl;
        }
        this.closeCamera();
        this.$refs.clockInForm.submit();
    },
    handleFallbackUpload(event) {
        const file = event.target.files[0];
        if (file) {
            this.cameraStatus = 'Memproses Absensi...';
            const reader = new FileReader();
            reader.onload = (e) => {
                this.$refs.imageDataInput.value = e.target.result;
                this.isFallback = 1;
                this.closeCamera();
                this.$refs.clockInForm.submit();
            };
            reader.readAsDataURL(file);
        }
    },
    init() {
        // Read GPS requirement from data attribute set by Blade
        this.isGpsRequired = this.$el.dataset.gpsRequired === '1';
        // Read today's day of week from data attribute (1=Mon...7=Sun, ISO), fallback to 1
        const todayIso = parseInt(this.$el.dataset.todayIso || '1', 10);
        this.activeDay = (todayIso >= 1 && todayIso <= 7) ? todayIso : 1;

        this.updateClock();
        setInterval(() => this.updateClock(), 1000);

        if (this.isGpsRequired) {
            this.isLoadingGps = true;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.lat = position.coords.latitude;
                        this.lng = position.coords.longitude;
                        this.isLoadingGps = false;
                    },
                    (error) => {
                        this.gpsError = 'Akses lokasi (GPS) wajib diaktifkan untuk melakukan absensi.';
                        this.isLoadingGps = false;
                    },
                    { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
                );
            } else {
                this.gpsError = 'Perangkat Anda tidak mendukung fitur GPS.';
                this.isLoadingGps = false;
            }
        }
    },
    updateClock() {
        const now = new Date();
        this.timeString = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        this.dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
}));

Alpine.start();
