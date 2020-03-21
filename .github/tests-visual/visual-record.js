/**
 * REDAXO Visual Regression testing
 * 
 * 1. Start a local php-server with `php -S localhost:8000` from within the project root.
 * 2. Make sure a database server is running 
 * 3. Make sure the REDAXO instance running at START_URL is accessible and login screen appears on the url
 * 3. Start the visual recording with `node .github/tests-visual/visual-record.js`
 */

const puppeteer = require('puppeteer');
const pixelmatch = require('pixelmatch');
const PNG = require('pngjs').PNG;
const fs = require('fs');
const mkdirp = require('mkdirp');

const screenshotWidth = 1280;
const screenshotHeight = 1024

const START_URL = 'http://localhost:8000/redaxo/index.php';
const DEBUGGING = false;
const MIN_DIFF_PIXELS = 1;
const WORKING_DIR = '.tests-visual/';
const GOLDEN_SAMPLES_DIR = '.github/tests-visual/';

// htaccess ajax checks are subject to race conditions and therefore generated 'random' markup.
// disable the check to get less visual noise.
const noHtaccessCheckCookie = {
    name: 'rex_htaccess_check',
    value: '1',
    domain: 'localhost',
    httpOnly: false,
    secure: false
};

function countDiffPixels(img1path, img2path ) {
    if (!fs.existsSync(img2path)) {
        // no reference image
        // we assume a new reference screenshot will be added
        return MIN_DIFF_PIXELS;
    }

    const img1 = PNG.sync.read(fs.readFileSync(img1path));
    const img2 = PNG.sync.read(fs.readFileSync(img2path));

    return pixelmatch(img1.data, img2.data, null, screenshotWidth, screenshotHeight, {threshold: 0.1});
}

async function createScreenshot(page, screenshotName) {
    mkdirp.sync(WORKING_DIR);

    // mask dynamic content, to make it not appear like change (visual noise)
    await page.evaluate(() => document.querySelector('.rex-js-script-time').innerHTML = 'XXX');

    await page.screenshot({ path: WORKING_DIR + screenshotName });

    // make sure we only create changes in .github/tests-visual/ on substential screenshot changes.
    // this makes sure to prevent endless loops within the github action
    let diffPixels = countDiffPixels(WORKING_DIR + screenshotName, GOLDEN_SAMPLES_DIR + screenshotName);
    console.log("DIFF-PIXELS: "+ screenshotName + ":" +diffPixels);
    if (diffPixels >= MIN_DIFF_PIXELS) {
        fs.renameSync(WORKING_DIR + screenshotName, GOLDEN_SAMPLES_DIR + screenshotName);
    }
}

async function main() {
    const options = { args: ['--no-sandbox', '--disable-setuid-sandbox'] };

    if (DEBUGGING) {
        // see https://developers.google.com/web/tools/puppeteer/debugging
        options.headless = false;
    }

    const browser = await puppeteer.launch(options);
    let page = await browser.newPage();
    // log browser errors into the console
    page.on('console', msg => console.log('BROWSER-CONSOLE:', msg.text()));

    await page.setViewport({ width: screenshotWidth, height: screenshotHeight });
    await page.setCookie(noHtaccessCheckCookie);

    await page.goto(START_URL);
    await new Promise(res => setTimeout(() => res(), 300));
    await createScreenshot(page, 'login.png');

    await page.type('#rex-id-login-user', 'myusername');
    await page.type('#rex-id-login-password', '91dfd9ddb4198affc5c194cd8ce6d338fde470e2'); // sha1('mypassword')
    await page.$eval('#rex-form-login', form => form.submit());
    await new Promise(res => setTimeout(() => res(), 5000));
    await createScreenshot(page, 'index.png');

    await page.close();
    await browser.close();
}

main();
