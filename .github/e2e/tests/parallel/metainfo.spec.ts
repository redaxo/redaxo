import {test, expect} from '@playwright/test';

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

test.describe.parallel('suite', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page}) => {
            await page.goto(item.url);
            expect(await page.screenshot({fullPage: true})).toMatchSnapshot(`${item.name}.png`);
        });
    }
});
