import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'cronjob_cronjobs',
        url: '?page=cronjob/cronjobs',
    },
    {
        name: 'cronjob_cronjobs_add',
        url: '?page=cronjob/cronjobs&func=add',
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
