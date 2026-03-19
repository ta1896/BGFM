import { chromium } from 'playwright';
import fs from 'node:fs/promises';
import path from 'node:path';

const baseUrl = process.env.APP_URL || 'http://localhost';
const email = process.env.README_SCREENSHOT_EMAIL || 'test.manager@openws.local';
const password = process.env.README_SCREENSHOT_PASSWORD || 'password';

const outputDir = path.resolve('docs', 'screenshots');

async function ensureDir() {
    await fs.mkdir(outputDir, { recursive: true });
}

async function login(page) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}

async function capture(page, route, fileName, width = 1600, height = 1000) {
    await page.setViewportSize({ width, height });
    await page.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
    await page.screenshot({
        path: path.join(outputDir, fileName),
        fullPage: true,
    });
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();

try {
    await ensureDir();
    await login(page);

    await capture(page, '/dashboard', 'dashboard.png', 1600, 1100);
    await capture(page, '/clubs/101', 'club.png', 1600, 1200);
    await capture(page, '/matches/3420', 'match-center.png', 1600, 1400);
} finally {
    await browser.close();
}
