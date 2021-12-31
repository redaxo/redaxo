import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'structure_category_edit',
        url: '?page=structure&category_id=0&article_id=0&clang=1&edit_id=1&function=edit_cat&catstart=0',
    },
    {
        name: 'structure_article_edit',
        url: '?page=content/edit&category_id=1&article_id=1&clang=1&mode=edit',
    },
    {
        name: 'structure_article_functions',
        url: '?page=content/functions&article_id=1&category_id=1&clang=1&ctype=1',
    },
    {
        name: 'structure_slice_edit',
        url: '?page=content/edit&article_id=1&slice_id=3&clang=1&ctype=1&function=edit#slice3',
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
