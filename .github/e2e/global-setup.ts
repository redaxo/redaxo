import {chromium, FullConfig} from '@playwright/test';

async function globalSetup(config: FullConfig) {
    const {baseURL, storageState} = config.projects[0].use;
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto(baseURL, {waitUntil: 'networkidle'});
    // fill out login form if available
    if (await page.locator('#rex-id-login-user').count() > 0) {
        console.log('login form!'); // TODO: remove
        await page.fill('#rex-id-login-user', 'admin');
        await page.fill('#rex-id-login-password', 'admin123');
        await page.click('#rex-id-login-stay-logged-in');
        await page.click('button:has-text("Login")');
        await page.waitForLoadState();
    }
    // Save signed-in state to 'storageState.json'
    await page.context().storageState({path: storageState as string});
    console.log(await page.context().storageState()); // TODO: remove
    await browser.close();
}

export default globalSetup;
