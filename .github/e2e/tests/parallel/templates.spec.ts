import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

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

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
