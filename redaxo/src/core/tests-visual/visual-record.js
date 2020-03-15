
const puppeteer = require('puppeteer');

async function main() {
    const options = { args: ['--no-sandbox', '--disable-setuid-sandbox'] };

    // uncomment for debugging
    // see https://developers.google.com/web/tools/puppeteer/debugging
    // options.headless = false;

    const browser = await puppeteer.launch(options);
    let page = await browser.newPage();
    page.on('console', msg => console.log('BROWSER-CONSOLE:', msg.text()));

    await page.setViewport({ width: 1280, height: 1024 });
    await page.goto(`http://localhost:8000/redaxo/index.php`);
    await new Promise(res => setTimeout(() => res(), 300));
    await page.screenshot({ path: 'redaxo/src/core/tests-visual/login.png' });

    await page.type('#rex-id-login-user', 'myusername');
    await page.type('#rex-id-login-password', '91dfd9ddb4198affc5c194cd8ce6d338fde470e2'); // sha1('mypassword')
    await page.$eval('#rex-form-login', form => form.submit());
    await new Promise(res => setTimeout(() => res(), 5000));

    await page.screenshot({ path: 'redaxo/src/core/tests-visual/index.png' });

    await page.close();
    await browser.close();
}

main();
