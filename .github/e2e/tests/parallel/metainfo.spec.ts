import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

const testItems = [
    {
        name: 'metainfo_articles',
        url: '?page=metainfo/articles',
    },
    {
        name: 'metainfo_articles_add',
        url: '?page=metainfo/articles&func=add',
    },
    {
        name: 'metainfo_categories',
        url: '?page=metainfo/categories',
    },
    {
        name: 'metainfo_media',
        url: '?page=metainfo/media',
    },
    {
        name: 'metainfo_clangs',
        url: '?page=metainfo/clangs',
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
