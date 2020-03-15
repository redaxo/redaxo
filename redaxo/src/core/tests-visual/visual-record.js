
const puppeteer = require('puppeteer');

const pixelmatch = require('pixelmatch');
const fs = require('fs');
const PNG = require('pngjs').PNG;

const screenshotWidth = 1280;
const screenshotHeight = 1024

const MIN_DIFF_PIXELS = 5;

function countDiffPixels(img1path, img2path ) {
    if (!fs.existsSync(img2path)) {
        // no reference image -> 0 pixel difference
        // we assume a new reference screenshot will be adaded
        return 0;
    }

    const img1 = PNG.sync.read(fs.readFileSync(img1path));
    const img2 = PNG.sync.read(fs.readFileSync(img2path));

    return pixelmatch(img1.data, img2.data, null, screenshotWidth, screenshotHeight, {threshold: 0.1});
}

async function createScreenshot(page, screenshotName) {
    await page.screenshot({ path: '.tests-visual/' + screenshotName });

    if (countDiffPixels('.tests-visual/' + screenshotName, 'redaxo/src/core/tests-visual/' + screenshotName) > MIN_DIFF_PIXELS) {
        fs.renameSync('.tests-visual/' + screenshotName, 'redaxo/src/core/tests-visual/' + screenshotName);
    }
}

async function main() {
    const options = { args: ['--no-sandbox', '--disable-setuid-sandbox'] };

    // uncomment for debugging
    // see https://developers.google.com/web/tools/puppeteer/debugging
    // options.headless = false;

    const browser = await puppeteer.launch(options);
    let page = await browser.newPage();
    page.on('console', msg => console.log('BROWSER-CONSOLE:', msg.text()));

    await page.setViewport({ width: screenshotWidth, height: screenshotHeight });
    await page.goto(`http://localhost:8000/redaxo/index.php`);
    await new Promise(res => setTimeout(() => res(), 300));
    await createScreenshot(page, 'login.png');
//    await page.screenshot({ path: 'redaxo/src/core/tests-visual/login.png' });

    await page.type('#rex-id-login-user', 'myusername');
    await page.type('#rex-id-login-password', '91dfd9ddb4198affc5c194cd8ce6d338fde470e2'); // sha1('mypassword')
    await page.$eval('#rex-form-login', form => form.submit());
    await new Promise(res => setTimeout(() => res(), 5000));

    await createScreenshot(page, 'index.png');

    await page.close();
    await browser.close();
}

main();
