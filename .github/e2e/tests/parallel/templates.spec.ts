import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'templates',
        url: '?page=templates',
    },
    {
        name: 'templates_add',
        url: '?page=templates&function=add',
    },
    {
        name: 'templates_edit',
        url: '?page=templates&function=edit&template_id=1',
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
