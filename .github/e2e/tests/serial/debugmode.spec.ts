import {expect, test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

test(`debugmode`, async ({page, browserName}, testInfo) => {
    // enable debug mode
    await gotoPage(page, browserName, `?page=system/settings`);
    await expect(page.locator('body')).not.toHaveClass(/rex-is-debugmode/); // fail fast
    page.on('dialog', dialog => dialog.accept()); // accept confirm dialogs
    await page.click('.btn-debug-mode');
    await page.locator('.alert-success').waitFor(); // wait for success message

    // snap index page
    await gotoPage(page, browserName, ''); // go to index page
    await matchPageSnapshot(page, `debugmode`, {fullPage: false});

    // disable debug mode again
    await gotoPage(page, browserName, `?page=system/settings`);
    await page.click('.btn-debug-mode');
    await page.locator('.alert-success').waitFor(); // wait for success message
});
