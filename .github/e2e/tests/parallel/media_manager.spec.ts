import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

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

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
