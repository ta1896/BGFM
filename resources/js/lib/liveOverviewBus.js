const listeners = new Set();
let started = false;
let loadingPromise = null;

async function ensureEchoLoaded() {
    if (window.Echo) {
        return window.Echo;
    }

    if (!loadingPromise) {
        loadingPromise = import('@/echo').then(() => window.Echo).catch(() => null);
    }

    return loadingPromise;
}

async function start() {
    if (started) {
        return;
    }

    const echo = await ensureEchoLoaded();
    if (!echo) {
        return;
    }

    started = true;

    echo.channel('live.overview').listen('.live.overview.updated', (event) => {
        listeners.forEach((listener) => listener(event));
    });
}

export function subscribeToLiveOverview(listener) {
    void start();
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}
