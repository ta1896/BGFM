import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const configuredHost = import.meta.env.VITE_REVERB_HOST;
const currentHost = window.location.hostname;
const localHosts = ['localhost', '127.0.0.1', '0.0.0.0'];
const shouldUseCurrentHost = !configuredHost || (localHosts.includes(configuredHost) && !localHosts.includes(currentHost));
const wsHost = shouldUseCurrentHost ? currentHost : configuredHost;
const scheme = import.meta.env.VITE_REVERB_SCHEME ?? (window.location.protocol === 'https:' ? 'https' : 'http');
const isSecure = scheme === 'https';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? (isSecure ? 443 : 80));

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost,
    wsPort: port,
    wssPort: port,
    forceTLS: isSecure,
    enabledTransports: ['ws', 'wss'],
});
