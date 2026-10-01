import { createInertiaApp } from '@inertiajs/vue3';

const defaultAppName = import.meta.env.VITE_APP_NAME || 'E-Voting SMKN 1 Talaga';

void createInertiaApp({
    title: (title) => {
        let pageAppName = defaultAppName;
        try {
            const pageData = document.getElementById('app')?.dataset?.page;
            if (pageData) {
                const parsed = JSON.parse(pageData);
                if (parsed?.props?.appSettings?.app_name) {
                    pageAppName = parsed.props.appSettings.app_name;
                }
            }
        } catch (e) {
            // fallback
        }

        const props = (window as any).page?.props;
        if (props?.appSettings?.app_name) {
            pageAppName = props.appSettings.app_name;
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
