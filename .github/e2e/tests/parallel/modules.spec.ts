import {test, expect} from '@playwright/test';

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

test.describe.parallel('suite', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page}) => {
            await page.goto(item.url);
            expect(await page.screenshot({fullPage: true})).toMatchSnapshot(`${item.name}.png`);
        });
    }
});
