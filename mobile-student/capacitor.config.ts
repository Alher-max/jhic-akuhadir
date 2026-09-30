import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'id.my.akuhadir.siswa',
  appName: 'AkuHadir Siswa',
  webDir: 'dist',
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
