import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';
import { LibraryProvider } from './contexts/LibraryContext';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        if (import.meta.env.SSR) {
            hydrateRoot(
                el,
                <LibraryProvider>
                    <App {...props} />
                </LibraryProvider>,
            );
            return;
        }

        createRoot(el).render(
            <LibraryProvider>
                <App {...props} />
            </LibraryProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
