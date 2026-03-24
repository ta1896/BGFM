import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const startEchoWhenIdle = () => {
    const loadEcho = () => {
        import('./echo');
    };

    if (document.visibilityState === 'visible') {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadEcho, { timeout: 2000 });
            return;
        }

        window.setTimeout(loadEcho, 1200);
        return;
    }

    const onVisible = () => {
        document.removeEventListener('visibilitychange', onVisible);
        loadEcho();
    };

    document.addEventListener('visibilitychange', onVisible);
};

startEchoWhenIdle();
