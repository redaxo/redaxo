import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'phpmailer_config',
        url: '?page=phpmailer/config',
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
