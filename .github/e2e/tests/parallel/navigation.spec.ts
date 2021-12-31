import {expect, test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

// hide page content and increase height for scrolling
const preparePageContent = async (page) => {
    await page.addStyleTag({
        content: `
          .rex-page-main {
            border: 20px solid yellow !important;
            visibility: hidden;
            height: 2000px;
          }
        `
    });
}

const openMobileNavigation = async (page) => {
    const toggleLocator = await page.locator('#rex-js-nav-main-toggle:visible');
    if (await toggleLocator.count() > 0) {
        await toggleLocator.click({force: true});
    }
}

test.describe.parallel('All', () => {

    test(`navigation`, async ({page, browserName}, testInfo) => {
        await gotoPage(page, browserName, `./`);
        await preparePageContent(page);
        await openMobileNavigation(page);
        await expect(page.locator('#rex-js-nav-top')).toHaveClass(/rex-nav-top-is-fixed/);
        await matchPageSnapshot(page, `${testInfo.title}`, {fullPage: false});
    });

    test(`navigation elevated`, async ({page, browserName}, testInfo) => {
        await gotoPage(page, browserName, `./`);
        await preparePageContent(page);
        await openMobileNavigation(page);
        await page.evaluate(() => window.scrollBy({top: 95, left: 0, behavior: 'smooth'}));
        await expect(page.locator('#rex-js-nav-top')).toHaveClass(/rex-nav-top-is-elevated/);
        await page.waitForTimeout(100); // wait for UI update
        await matchPageSnapshot(page, `${testInfo.title}`, {fullPage: false});
    });

    test(`navigation hidden`, async ({page, browserName}, testInfo) => {
        await gotoPage(page, browserName, `./`);
        await preparePageContent(page);
        await page.evaluate(() => window.scrollBy({top: 300, left: 0, behavior: 'smooth'}));
        await expect(page.locator('#rex-js-nav-top')).toHaveClass(/rex-nav-top-is-hidden/);
        await page.waitForTimeout(100); // wait for UI update
        await matchPageSnapshot(page, `${testInfo.title}`, {fullPage: false});
    });
});
