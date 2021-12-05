import {test, expect} from '@playwright/test';

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

test.describe.parallel('suite', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page}) => {
            await page.goto(item.url);
            expect(await page.screenshot({fullPage: true})).toMatchSnapshot(`${item.name}.png`);
        });
    }
});
