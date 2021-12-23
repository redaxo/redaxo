import {Page} from '@playwright/test';
import {maskContent, stopAnimations, waitForImagesLazyloaded} from './layout';

export const gotoPage = async (page: Page, browserName: string, slug: string, options = {}) => {
    const response = await page.goto(`${slug}`, {
        ...options
    });
    if (!response.ok() && response.status() != 304) {
        throw new Error(`Failed to load ${slug}: the server responded with a status of ${response.status()} (${response.statusText()})`);
    }
    await stopAnimations(page);
    await maskContent(page);
    await waitForImagesLazyloaded(page);
};
