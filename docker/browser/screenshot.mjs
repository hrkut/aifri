// Takes screenshots of pages of the running app.
//
//   docker compose -p aifri run --rm browser [options] <path-or-url> [<path-or-url> ...]
//
// Paths are resolved against BASE_URL (default http://laravel.test, the app container
// on the compose network). Files are written to /shots (./.screenshots on the host).
//
// Options:
//   --width=1280        viewport width (repeat or use commas for several: --width=1280,390)
//   --height=900        viewport height
//   --full              full-page screenshot instead of the viewport only
//   --motion            keep animations (default: prefers-reduced-motion, so pages are static)
//   --name=prefix       file name prefix (default derived from the path)
//   --login=user:pass   log in to /login first (for admin pages)
//   --recordings=pass   log in to /zaznamy first (recordings password)

import puppeteer from 'puppeteer-core';
import { mkdirSync } from 'node:fs';

const BASE_URL = (process.env.BASE_URL || 'http://laravel.test').replace(/\/$/, '');
const OUT_DIR = process.env.OUT_DIR || '/shots';

const opts = { widths: [], height: 900, full: false, motion: false, name: null, login: null, recordings: null };
const targets = [];

for (const arg of process.argv.slice(2)) {
    const [key, value] = arg.startsWith('--') ? arg.slice(2).split(/=(.*)/s) : [null, arg];
    switch (key) {
        case null: targets.push(value); break;
        case 'width': opts.widths.push(...value.split(',').map(Number)); break;
        case 'height': opts.height = Number(value); break;
        case 'full': opts.full = true; break;
        case 'motion': opts.motion = true; break;
        case 'name': opts.name = value; break;
        case 'login': opts.login = value; break;
        case 'recordings': opts.recordings = value; break;
        default: console.error(`Unknown option --${key}`); process.exit(2);
    }
}

if (targets.length === 0) {
    console.error('Usage: screenshot.mjs [--width=1280,390] [--full] [--login=user:pass] <path-or-url> ...');
    process.exit(2);
}
if (opts.widths.length === 0) opts.widths = [1280];

const toUrl = (t) => (/^https?:\/\//.test(t) ? t : `${BASE_URL}/${t.replace(/^\//, '')}`);
const slug = (t) => (t.replace(/^https?:\/\/[^/]+/, '').replace(/^\/|\/$/g, '').replace(/[^a-z0-9]+/gi, '-') || 'home');

mkdirSync(OUT_DIR, { recursive: true });

const browser = await puppeteer.launch({
    executablePath: process.env.PUPPETEER_EXECUTABLE_PATH,
    headless: true,
    args: [
        '--no-sandbox',              // containers have no user namespaces for the sandbox
        '--disable-dev-shm-usage',   // do not rely on a large /dev/shm
        '--disable-gpu',             // no GPU in WSL/Docker Desktop VMs
        '--font-render-hinting=none',
    ],
});

async function submitForm(page, path, fields) {
    await page.goto(toUrl(path), { waitUntil: 'networkidle0' });
    for (const [selector, value] of Object.entries(fields)) {
        await page.type(selector, value);
    }
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.$eval('form', (f) => f.submit())]);
}

let failed = false;
try {
    const page = await browser.newPage();
    if (!opts.motion) {
        await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    }

    if (opts.login) {
        const [email, ...rest] = opts.login.split(':');
        await submitForm(page, '/login', { 'input[name="email"]': email, 'input[name="password"]': rest.join(':') });
    }
    if (opts.recordings) {
        await submitForm(page, '/zaznamy', { 'input[name="password"]': opts.recordings });
    }

    for (const target of targets) {
        for (const width of opts.widths) {
            await page.setViewport({ width, height: opts.height, deviceScaleFactor: 1 });
            const response = await page.goto(toUrl(target), { waitUntil: 'networkidle0', timeout: 30000 });
            await page.evaluate(() => document.fonts.ready);

            const file = `${OUT_DIR}/${opts.name ?? slug(target)}-${width}.png`;
            await page.screenshot({ path: file, fullPage: opts.full });

            const status = response?.status() ?? 0;
            if (status >= 400) failed = true;
            console.log(`${status} ${toUrl(target)} @${width}px -> ${file.replace(OUT_DIR, '.screenshots')}`);
        }
    }
} catch (e) {
    console.error(e.message);
    failed = true;
} finally {
    await browser.close();
}

process.exit(failed ? 1 : 0);
