import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'media_manager_types',
        url: '?page=media_manager/types',
    },
    {
        name: 'media_manager_types_add',
        url: '?page=media_manager/types&func=add',
    },
    {
        name: 'media_manager_types_edit',
        url: '?page=media_manager/types&type_id=4&effects=1',
    },
    {
        name: 'media_manager_settings',
        url: '?page=media_manager/settings',
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
