import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'packages',
        url: '?page=packages',
    },
    {
        name: 'packages_help',
        url: '?page=packages&subpage=help&package=project',
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
