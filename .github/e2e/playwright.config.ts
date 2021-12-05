import {PlaywrightTestConfig} from '@playwright/test';

const config: PlaywrightTestConfig = {

    // global setup
    globalSetup: require.resolve('./global-setup'),
    preserveOutput: 'never',
    reportSlowTests: null,

    // basic options
    // https://playwright.dev/docs/test-configuration#basic-options
    use: {
        baseURL: 'http://localhost/redaxo/index.php',
        headless: true,
        // Tell all tests to load signed-in state from 'storageState.json'
        // https://playwright.dev/docs/test-auth#reuse-signed-in-state
        storageState: 'storageState.json'
    },

    // multiple browsers
    // https://playwright.dev/docs/test-configuration#multiple-browsers
    projects: [
        {
            // Desktop Chrome (Light)
            name: 'chromium',
            use: {
                browserName: 'chromium',
                viewport: {width: 1280, height: 600},
                colorScheme: 'light',
                deviceScaleFactor: 1,
                hasTouch: false,
                isMobile: false,
            },
        },
        {
            // Mobile Safari (Dark)
            name: 'webkit',
            use: {
                browserName: 'webkit',
                viewport: {width: 400, height: 600},
                colorScheme: 'dark',
                deviceScaleFactor: 2,
                hasTouch: true,
                isMobile: true,
            },
        },
    ],
};

export default config;
