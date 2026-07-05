import '../css/app.css';

import { LibraryProvider } from '@/contexts/LibraryContext';
import { createInertiaApp } from '@inertiajs/react';
import CssBaseline from '@mui/material/CssBaseline';
import { createTheme, ThemeProvider } from '@mui/material/styles';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const lightTheme = createTheme({
    palette: {
        mode: 'light',
        background: {
            default: '#f3f4f6',
            paper: '#ffffff',
        },
        text: {
            primary: '#111827',
            secondary: '#4b5563',
        },
    },
    components: {
        MuiCssBaseline: {
            styleOverrides: {
                body: {
                    backgroundColor: '#f3f4f6',
                    color: '#111827',
                },
            },
        },
    },
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const appWithProviders = (
            <ThemeProvider theme={lightTheme}>
                <CssBaseline />
                <LibraryProvider>
                    <App {...props} />
                </LibraryProvider>
            </ThemeProvider>
        );

        if (import.meta.env.SSR) {
            hydrateRoot(el, appWithProviders);
            return;
        }

        createRoot(el).render(appWithProviders);
    },
    progress: {
        color: '#4B5563',
    },
});
