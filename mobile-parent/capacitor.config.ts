import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'id.my.akuhadir.ortu',
  appName: 'AkuHadir Orang Tua',
  webDir: 'www',
  server: {
    url: 'https://akuhadir.my.id',
    cleartext: false,
    allowNavigation: ['akuhadir.my.id', '*.akuhadir.my.id'],
  },
  android: {
    backgroundColor: '#ffffff',
  },
};

export default config;
