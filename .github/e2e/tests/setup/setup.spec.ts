import { test } from '@playwright/test';
import { gotoPage, matchPageSnapshot } from "../../lib";

test.use({ storageState: undefined }); // do not use signed-in state from 'storageState.json'

// setup steps 1-6
test.describe.parallel('All', () => {
    for (let step = 1; step <= 6; step++) {
        test(`setup_step_${step}`, async ({ page, browserName }) => {
            await gotoPage(page, browserName, `?page=setup&lang=de_de&step=${step}`);
            await matchPageSnapshot(page, `setup_step_${step}`);
        });
    }
});

// skip setup step 7 since it requires form in step 6 to be submitted and it
// disables REDAXO’s setup mode. in order to run all browsers with step 7, we
// would have to reset REDAXO every time before or use separate workflows.
// so let’s just skip step 7 for now…
