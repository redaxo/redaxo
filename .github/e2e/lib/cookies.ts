import {Page} from '@playwright/test';

export const disableHtaccessCheckCookie = async (page: Page) => {
    // htaccess ajax checks are subject to race conditions and therefore generated 'random' markup.
    // disable the check to get less visual noise.
    await page.context().addCookies([{
        name: 'rex_htaccess_check',
        value: '1',
        domain: 'localhost',
        path: '/',
        httpOnly: false,
        secure: false
    }]);
};
