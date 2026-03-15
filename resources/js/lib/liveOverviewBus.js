const listeners = new Set();
let started = false;

function start() {
    if (started || !window.Echo) {
        return;
    }

    started = true;

    window.Echo.channel('live.overview').listen('.live.overview.updated', (event) => {
        listeners.forEach((listener) => listener(event));
    });
}

export function subscribeToLiveOverview(listener) {
    start();
    listeners.add(listener);

    return () => {
        listeners.delete(listener);
    };
}
