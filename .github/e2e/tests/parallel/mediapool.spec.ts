import {test, expect} from '@playwright/test';

const testItems = [
    {
        name: 'mediapool_media',
        url: '?page=mediapool/media',
    },
    {
        name: 'mediapool_media_file',
        url: '?page=mediapool/media&file_id=1&rex_file_category=0',
    },
    {
        name: 'mediapool_upload',
        url: '?page=mediapool/upload',
    },
    {
        name: 'mediapool_structure',
        url: '?page=mediapool/structure',
    },
    {
        name: 'mediapool_sync',
        url: '?page=mediapool/sync',
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
