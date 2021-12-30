import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

test(`impersonate`, async ({page, browserName}, testInfo) => {
    // impersonate
    await gotoPage(page, browserName, `?page=users/users`);
    await page.click('[href*="?page=users/users&_impersonate=2"]');
    await page.locator('.rex-is-impersonated').waitFor({state: 'attached'});

    // snap index page
    await matchPageSnapshot(page, `impersonate`, {fullPage: false});

    // depersonate
    await page.evaluate(() => document.body.classList.add('rex-nav-main-is-visible')); // switch mobile header to show meta elements
    await page.click('[href*="&_impersonate=_depersonate"]');
    await page.locator('.rex-is-impersonated').waitFor({state: 'detached'});
});
