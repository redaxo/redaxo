import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'users_users',
        url: '?page=users/users',
    },
    {
        name: 'users_edit',
        url: '?page=users/users&user_id=1',
    },
    {
        name: 'users_roles',
        url: '?page=users/roles',
    },
    {
        name: 'users_role_add',
        url: '?page=users/roles&func=add&default_value=1',
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
