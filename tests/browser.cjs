require("fs").mkdirSync("screenshots", { recursive: true });
const { chromium } = require("playwright");
(async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || "/usr/bin/chromium",
    headless: true,
    args: ["--no-sandbox"],
  });
  const page = await browser.newPage({
    viewport: { width: 1440, height: 1000 },
  });
  const errors = [];
  page.on("pageerror", (e) => errors.push(e.message));
  const base = process.env.BASE_URL || "http://127.0.0.1:8080/";
  // Check both states: a page can fit while content inside a scroll box is clipped.
  async function checkDirectory(isEmpty) {
    await page.goto(base + "view.php");
    for (const width of [320, 390, 514, 1440]) {
      await page.setViewportSize({ width, height: 844 });
      if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth))
        throw Error(`directory page overflow at ${width}px`);
      if (isEmpty) {
        if (await page.locator("table").count())
          throw Error("empty state must not be inside the wide directory table");
        for (const element of [
          page.getByRole("heading", { name: "A community starts with one person." }),
          page.getByRole("link", { name: "Add the first volunteer" }),
        ]) {
          const bounds = await element.boundingBox();
          if (!bounds || bounds.x < 0 || bounds.x + bounds.width > width)
            throw Error(`empty state clipped at ${width}px`);
        }
      } else {
        const scrollBox = page.locator(".table-responsive");
        await scrollBox.evaluate(element => { element.scrollLeft = element.scrollWidth; });
        const actions = page.getByRole("link", { name: "Delete", exact: true });
        const box = await scrollBox.boundingBox();
        const action = await actions.boundingBox();
        if (!action || action.x < box.x || action.x + action.width > box.x + box.width + 1)
          throw Error(`directory actions unreachable at ${width}px`);
        await scrollBox.evaluate(element => { element.scrollLeft = 0; });
      }
      if (width === 514) await page.screenshot({
        path: `screenshots/directory-${isEmpty ? "empty" : "populated"}-514.png`, fullPage: true,
      });
    }
    await page.setViewportSize({ width: 1440, height: 1000 });
  }
  await checkDirectory(true);
  await page.goto(base);
  if (!(await page.locator('.brand-logo').evaluate(image => image.complete && image.naturalWidth > 0)))
    throw Error('brand logo did not load');
  await page.screenshot({
    path: "screenshots/01-registration.png",
    fullPage: true,
  });
  await page.locator("button[type=submit]").click();
  if (await page.locator("#volunteer_name").evaluate((e) => e.validity.valid))
    throw Error("required validation");
  for (const [k, v] of Object.entries({
    volunteer_name: "Sarah Lim",
    ic_number: "920101-14-5678",
    contact_number: "012-3456789",
    email: "sarah.lim@example.com",
    available_date: "2026-11-22",
  }))
    await page.locator("#" + k).fill(v);
  await page.selectOption("#service_category", "Environmental Care");
  await page.locator("button[type=submit]").click();
  await page.waitForURL("**/success.php");
  await page.screenshot({ path: "screenshots/02-success.png", fullPage: true });
  await checkDirectory(false);
  await page.screenshot({
    path: "screenshots/03-directory.png",
    fullPage: true,
  });
  await page.getByRole("link", { name: "Edit", exact: true }).click();
  await page.locator("#volunteer_name").fill("Sarah Lim Updated");
  await page.locator("button[type=submit]").click();
  await page.waitForURL("**/view.php");
  if (!(await page.getByText("Sarah Lim Updated", { exact: true }).count()))
    throw Error("edit");
  await page.getByRole("link", { name: "Delete", exact: true }).click();
  await page.screenshot({ path: "screenshots/04-delete.png", fullPage: true });
  await page
    .getByRole("button", { name: "Delete registration", exact: true })
    .click();
  await page.waitForURL("**/view.php");
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(base);
  if (!(await page.locator('.brand-logo').evaluate(image => image.complete && image.naturalWidth > 0)))
    throw Error('brand logo did not load');
  if (
    await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)
  )
    throw Error("mobile overflow");
  await page.screenshot({ path: "screenshots/05-mobile.png", fullPage: true });
  if (errors.length) throw Error(errors.join("\n"));
  console.log(
    "PASS: browser required validation, registration, success details, edit, delete, 390px registration, empty/populated directory at 320/390/514/1440px, no JavaScript errors",
  );
  await browser.close();
})();
