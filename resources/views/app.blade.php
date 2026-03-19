<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#d9b15c">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="alternate icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', async () => {
                    const isLocalhost = ['localhost', '127.0.0.1'].includes(window.location.hostname);

                    if (isLocalhost) {
                        const registrations = await navigator.serviceWorker.getRegistrations();
                        await Promise.all(registrations.map((registration) => registration.unregister()));

                        if ('caches' in window) {
                            const cacheNames = await caches.keys();
                            await Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)));
                        }

                        return;
                    }

                    navigator.serviceWorker.register('/sw.js');
                });
            }
        </script>
        @routes
        @viteReactRefresh
        @php
            $componentPath = "resources/js/Pages/{$page['component']}.jsx";
            if (str_starts_with($page['component'], 'Modules/')) {
                $parts = explode('/', $page['component']);
                $module = $parts[1];
                $subPath = implode('/', array_slice($parts, 2));
                $componentPath = "modules/{$module}/resources/js/Pages/{$subPath}.jsx";
            }
        @endphp
        @vite(['resources/js/app.jsx', $componentPath])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-[#0a0b0d] text-white">
        @inertia
    </body>
</html>
