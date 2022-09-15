import { expect, test } from '@playwright/test';
import { gotoPage, matchPageSnapshot } from "../../lib";

const testItems = [
    {
        name: 'login_logout',
        url: '?rex_logged_out=1',
    },
]

test.use({ storageState: undefined }); // do not use signed-in state from 'storageState.json'

test.describe.parallel('All', () => {
    for (const item of testItems) {

        // TODO: wait for playwright issue to be fixed: https://github.com/microsoft/playwright/issues/15977#issuecomment-1246890654
        test.fixme(`${item.name}`, async ({ page, browserName }, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await expect(page.locator('.rex-background')).toHaveClass(/rex-background--ready/);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
