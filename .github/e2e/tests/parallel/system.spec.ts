import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'system_settings',
        url: '?page=system/settings',
    },
    {
        name: 'system_lang',
        url: '?page=system/lang',
    },
    {
        name: 'system_log',
        url: '?page=system/log/redaxo',
    },
    {
        name: 'system_report',
        url: '?page=system/report/html',
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
