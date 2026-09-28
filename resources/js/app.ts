import { createInertiaApp } from '@inertiajs/vue3';

const defaultAppName = import.meta.env.VITE_APP_NAME || 'LabAntik Pemilu SMKN 1 Talaga';

void createInertiaApp({
    title: (title) => {
        const props = (window as any).page?.props;
        const pageAppName = props?.appSettings?.app_name || defaultAppName;
        const favicon = props?.appSettings?.app_favicon;

        if (favicon) {
            const iconLinks = document.querySelectorAll("link[rel*='icon']");
            iconLinks.forEach((link: any) => {
                if (link) {
                    link.href = favicon;
                }
            });
        }

        return title ? `${title} - ${pageAppName}` : pageAppName;
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});
