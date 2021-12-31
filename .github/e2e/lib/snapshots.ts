import {expect, Page} from '@playwright/test';

export const matchPageSnapshot = async (page: Page, snapshotTitle: string, options = {}) => {
    const image = await page.screenshot({fullPage: true, ...options});
    return expect(image).toMatchSnapshot(`${snapshotTitle}.png`, {threshold: 0.2});
};
