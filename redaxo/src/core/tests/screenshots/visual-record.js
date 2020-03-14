
const puppeteer = require('puppeteer');

async function main() {
    const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
    let page = await browser.newPage();

    await page.setViewport({ width: 640, height: 680 });
    // await page.goto(`file://${__dirname}/index.html`);
    await page.goto(`http://localhost/redaxo/redaxo/index.php`);
    await new Promise(res => setTimeout(() => res(), 300));
    await page.screenshot({ path: 'redaxo/src/core/tests/screenshots/index.png' });

    await page.close();
    await browser.close();
}

main();
