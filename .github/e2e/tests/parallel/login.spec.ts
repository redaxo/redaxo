import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'login_logout',
        url: '?rex_logged_out=1',
    },
]

test.use({storageState: undefined}); // do not use signed-in state from 'storageState.json'

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await page.locator('.rex-background--ready').waitFor({state: 'attached'}); // wait for bg image
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
