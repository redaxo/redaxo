import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'login_logout',
        url: '?rex_logged_out=1',
    },
]

test.describe.parallel('All', () => {
    test.use({ storageState: undefined }); // logout
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
