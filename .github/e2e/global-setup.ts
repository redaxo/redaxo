import {chromium, FullConfig} from '@playwright/test';

async function globalSetup(config: FullConfig) {
    const { baseURL, storageState } = config.projects[0].use;
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto(baseURL!);
    await page.fill('#rex-id-login-user', 'myusername');
    await page.fill('#rex-id-login-password', 'mypassword');
    await page.click('#rex-id-login-stay-logged-in');
    await page.click('button:has-text("Login")');
    // Save signed-in state to 'storageState.json'
    await page.context().storageState({ path: storageState as string });
    await browser.close();
}

export default globalSetup;
