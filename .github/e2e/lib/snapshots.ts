import { expect, Page } from '@playwright/test';

export const matchPageSnapshot = async (page: Page, snapshotTitle: string, options = {}) => {
    const image = await page.screenshot({ fullPage: true, animations: 'disabled', ...options });
    return expect(image).toMatchSnapshot(`${snapshotTitle}.png`, { threshold: 0.25 });
};
