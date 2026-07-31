// Register Service Worker and Auto-Subscribe
if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', async () => {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (Notification.permission === 'granted' && !subscription) {
            console.log('Permission granted but not subscribed, subscribing...');
            await window.PushManager.subscribe();
        }
    });
}

// PWA Push Subscription Utility
window.PushManager = {
    isSupported: () => {
        return 'serviceWorker' in navigator && 'PushManager' in window;
    },

    async subscribe() {
        if (!this.isSupported()) {
            console.error('Push notification not supported');
            return;
        }

        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
        if (!vapidPublicKey) {
            console.error('VAPID public key not found');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
            });

            await this.saveSubscription(subscription);
            return subscription;
        } catch (error) {
            console.error('Push subscription failed:', error);
            throw error;
        }
    },

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    },

    async saveSubscription(subscription) {
        const response = await fetch('/push-subscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(subscription)
        });

        if (!response.ok) {
            throw new Error('Failed to save subscription');
        }

        console.log('Push subscription saved successfully');
    },

    async unsubscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            if (subscription) {
                await subscription.unsubscribe();
                await fetch('/push-subscription', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint })
                });
            }
        } catch (error) {
            console.error('Unsubscribe failed:', error);
        }
    }
};
