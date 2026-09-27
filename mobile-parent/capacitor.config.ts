import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'shop.thortech.hadiryuk.parent',
  appName: 'HadirYuk Orang Tua',
  webDir: 'www',
  server: {
    url: 'https://hadiryuk.thortech.shop/parent/dashboard',
    cleartext: false,
    allowNavigation: ['hadiryuk.thortech.shop', '*.thortech.shop'],
  },
  android: {
    backgroundColor: '#ffffff',
  },
};

export default config;
