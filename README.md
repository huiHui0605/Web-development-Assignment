# The Heart community hub — Volunteer Registration Management System
BTPR2113 Web Development, 2026 C. PHP 8.3, MySQL-compatible MariaDB 11.4, Bootstrap 5.3.3 (bundled locally).

## Run with Docker
Run `docker compose up -d --build` from this directory. Open port 8080 locally in a browser. Stop with `docker compose down`; the named volume retains registrations. Rebuild after source changes. `docker compose down -v` deletes the database; use only for disposable data.

## Run with XAMPP / PHP and MySQL
1. Copy `VolunteerSystem` into your web server document root (e.g. XAMPP `htdocs`).
2. Import `VolunteerSystem/database.sql` using phpMyAdmin or the MySQL client.
3. Enable PHP PDO MySQL. The defaults are host `127.0.0.1`, database `volunteer_db`, user `root`, blank password, suitable for default local XAMPP. Set `DB_HOST`, `DB_USER`, and `DB_PASSWORD` in the server environment for other installations.
4. Open `VolunteerSystem/index.php` through the web server, not as a local file.

## Features and implementation
- `index.php`: six required form fields, Bootstrap responsive design, POST to `save.php`.
- `config.php`: PDO configuration, validation, session CSRF token, HTML escaping.
- `save.php` / `success.php`: validated prepared INSERT, redirect after POST and submitted-details confirmation.
- `view.php`: responsive directory, all fields, 50 records per page, descending registration ID, Previous/Next/page numbers.
- `edit.php` / `delete.php`: prepared UPDATE/DELETE; deletion requires confirmation and POST.
- `database.sql`: required seven-column schema with auto-increment primary key.
- Local Bootstrap CSS and original SVG illustration work without a CDN connection.

## Verification
Use a fresh disposable database, then run `python3 tests/smoke.py`. This creates 51 fictional registrations, tests validation/security/CRUD/pagination and removes those records on success. Do not run against a populated database. Optional `BASE_URL` overrides the default local port 8080. PHP lint: `docker compose exec -T web sh -c 'for f in /var/www/html/*.php; do php -l "$f" || exit; done'`.

## Scope and decisions
The assignment requests a local demonstration, not a public deployment. Listing includes IC and contact data and has no login/role system; use fictional data. Add authentication, authorisation, HTTPS, credential management and privacy controls before real deployment. Compose credentials are local demonstration values. Database exceptions during connection/insertion return generic errors; configure production error logging separately. Valid historical availability dates are allowed because the brief does not require future dates. Registration order uses the monotonically increasing ID because the specified schema has no timestamp. Duplicate ICs are permitted: uniqueness was not requested. The report and submission ZIP are delivered separately, outside this repository.

## Third-party asset
Bootstrap 5.3.3, Copyright 2011–2024 The Bootstrap Authors, MIT license; see `VolunteerSystem/assets/bootstrap.LICENSE`.

Optional browser regression: provide Node.js, Playwright and Chromium (default `/usr/bin/chromium`), then run `node tests/browser.cjs` against an empty local database. Screenshots are written to ignored `screenshots/`. It creates and removes a fictional Sarah Lim registration. No report files are committed.

## Reading and explaining the code
The PHP templates and custom CSS are expanded into readable blocks. `config.php`
shares the brand name, page size, labels, length limits, validation and security
helpers. `layout.php` shares the header, footer and registration/edit form.
English comments explain the connection, prepared SQL, CSRF, CRUD and pagination.
The bundled third-party Bootstrap distribution remains minified.

The supplied logo is stored at `VolunteerSystem/images/brand/logo.png`.
The shared header displays the original PNG on every page alongside the brand name.
The existing community illustration remains part of the registration page.

### Refactor verification
Verified locally with PHP 8.3 and MariaDB 11.4 using a disposable database:
- All eight PHP files pass syntax checking.
- HTTP tests create 51 registrations and verify 50/1 pagination, ordering and page bounds.
- Validation, CSRF rejection on create/edit/delete, edit persistence, HTML escaping,
  delete confirmation and deletion pass; test records are removed.
- Chromium tests pass registration, editing, deletion, required fields and a 390px viewport.

The default Apache image build exceeded this test environment's Docker storage.
Runtime checks used PHP's built-in server in a temporary Alpine container with PDO MySQL,
connected to the repository's MariaDB image. The repository Docker files are unchanged.
For browser testing, `CHROMIUM_PATH` may select another installed Chromium binary and
`BASE_URL` may select another local server.
