import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';
const corePages = import.meta.glob('./Pages/**/*.jsx');
const modulePages = import.meta.glob('../../modules/**/resources/js/Pages/**/*.jsx');

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        if (name.startsWith('Modules/')) {
            const [, moduleName, ...pageSegments] = name.split('/');
            const modulePath = `../../modules/${moduleName}/resources/js/Pages/${pageSegments.join('/')}.jsx`;
            return resolvePageComponent(modulePath, modulePages);
        }

        return resolvePageComponent(`./Pages/${name}.jsx`, corePages);
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#d9b15c',
    },
});
