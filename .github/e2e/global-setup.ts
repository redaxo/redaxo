import {chromium, FullConfig} from '@playwright/test';

async function globalSetup(config: FullConfig) {
    const {baseURL, storageState} = config.projects[0].use;
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto(baseURL, {waitUntil: 'networkidle'});
    // fill out login form if available
    if (await page.locator('#rex-form-login').count() > 0) {
        await page.fill('#rex-id-login-user', 'admin');
        await page.fill('#rex-id-login-password', 'admin123');
        await page.check('#rex-id-login-stay-logged-in');
        await page.click('button[type=submit]');
        await page.waitForLoadState();
    }
    // Save signed-in state to 'storageState.json'
    await page.context().storageState({path: storageState as string});
    console.log(await page.context().cookies());
    await browser.close();
}

export default globalSetup;
