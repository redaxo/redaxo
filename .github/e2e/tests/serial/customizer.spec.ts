import {expect, test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";
import {selectors} from "playwright";

test(`customizer`, async ({page, browserName}, testInfo) => {
    // enable customizer
    await gotoPage(page, browserName, `?page=packages`);
    await expect(page.locator('#package-be_style-customizer')).toHaveClass(/rex-package-not-installed/); // fail fast
    await page.click('[href*="&package=be_style/customizer&function=install"]');
    await page.locator('.alert-success').waitFor(); // wait for success message

    // snap index page
    await gotoPage(page, browserName, ''); // go to index page
    await matchPageSnapshot(page, `customizer`, {fullPage: false});

    // disable customizer again
    await gotoPage(page, browserName, `?page=packages`);
    page.on('dialog', dialog => dialog.accept()); // accept confirm dialogs
    await page.click('[href*="&package=be_style/customizer&function=uninstall"]');
    await page.locator('.alert-success').waitFor(); // wait for success message
});
