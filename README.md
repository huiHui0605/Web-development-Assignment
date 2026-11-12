# Kindred — Volunteer Registration Management System
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
