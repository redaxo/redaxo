import {test, expect} from '@playwright/test';

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

test.describe.parallel('suite', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page}) => {
            await page.goto(item.url);
            expect(await page.screenshot({fullPage: true})).toMatchSnapshot(`${item.name}.png`);
        });
    }
});
