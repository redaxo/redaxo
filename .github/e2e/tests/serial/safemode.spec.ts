import {expect, test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

test(`safemode`, async ({page, browserName}, testInfo) => {
    // activate safe mode
    await gotoPage(page, browserName, `?page=system/settings`);
    await page.click('.btn-safemode-activate');
    await page.locator('.rex-is-safemode').waitFor({state: 'attached'});

    // snap index page
    await matchPageSnapshot(page, `safemode`, {fullPage: false});

    // deactivate safe mode again
    await page.evaluate(() => document.body.classList.add('rex-nav-main-is-visible')); // switch mobile header to show meta elements
    await page.click('.btn-safemode-deactivate');
    await page.locator('.rex-is-safemode').waitFor({state: 'detached'});
});
