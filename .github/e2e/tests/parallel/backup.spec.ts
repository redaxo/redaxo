import {test, expect} from '@playwright/test';

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

test.describe.parallel('suite', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page}) => {
            await page.goto(item.url);
            expect(await page.screenshot({fullPage: true})).toMatchSnapshot(`${item.name}.png`);
        });
    }
});
