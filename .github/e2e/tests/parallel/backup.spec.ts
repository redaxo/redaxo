import { test } from '@playwright/test';
import { gotoPage, matchPageSnapshot } from "../../lib";

const testItems = [
    {
        name: 'backup_export',
        url: '?page=backup/export',
    },
    {
        name: 'backup_import',
        url: '?page=backup/import',
    },
    {
        name: 'backup_import_server',
        url: '?page=backup/import/server',
    },
]

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({ page, browserName }, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
