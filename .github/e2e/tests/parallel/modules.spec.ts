import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'modules_modules',
        url: '?page=modules/modules',
    },
    {
        name: 'modules_modules_add',
        url: '?page=modules/modules&function=add',
    },
    {
        name: 'modules_actions',
        url: '?page=modules/actions',
    },
    {
        name: 'modules_actions_add',
        url: '?page=modules/actions&function=add',
    },
]

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
