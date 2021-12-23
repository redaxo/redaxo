import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

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

test.describe.parallel('All', () => {
    for (const item of testItems) {

        test(`${item.name}`, async ({page, browserName}, testInfo) => {
            await gotoPage(page, browserName, `${item.url}`);
            await matchPageSnapshot(page, `${testInfo.title}`);
        });
    }
});
