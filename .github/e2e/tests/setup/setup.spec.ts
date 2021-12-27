import {test} from '@playwright/test';
import {gotoPage, matchPageSnapshot} from "../../lib";

// setup steps 1-6
test.describe.parallel('All', () => {
    for (let step = 1; step <= 6; step++) {
        test(`setup_step_${step}`, async ({page, browserName}) => {
            await gotoPage(page, browserName, `?page=setup&lang=de_de&step=${step}`);
            await matchPageSnapshot(page, `setup_step_${step}`);
        });
    }
});

// setup step 7
// requires form in step 6 to be submitted
test(`setup_step_7`, async ({page, browserName}) => {
    await gotoPage(page, browserName, `?page=setup&lang=de_de&step=6`);
    await page.click('button[type="submit"]');
    await page.locator('.btn-setup').waitFor(); // wait for login button
    await matchPageSnapshot(page, `setup_step_7`);
});
