import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.thortech.hadiryuk.student',
  appName: 'HadirYuk Siswa',
  webDir: 'dist',
  server: {
    url: 'https://hadiryuk.thortech.shop/pwa/clock-in',
    cleartext: false,
    allowNavigation: ['hadiryuk.thortech.shop', '*.thortech.shop'],
  },
  android: {
    backgroundColor: '#ffffff',
  },
};

export default config;
