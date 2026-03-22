# Fresh — Event Management (Short Overview)

This is a lightweight PHP-based event management web application intended to run on a local XAMPP (Apache + MySQL) stack.

Key ideas
- Simple PHP pages with shared header/footer included from `includes/`.
- Admin area under `admin/` for CRUD on events, categories and revenue management.
- Uses `includes/db_connect.php` for database connections and `includes/functions.php` for helpers.
- Email functionality provided via `Mail.php` and bundled PHPMailer in `vendor/phpmailer`.

Main folders & files
- `index.php` — landing page and event listings.
- `events.php`, `event_details.php` — event browsing and details.
- `register.php`, `login.php`, `logout.php`, `forget_pass.php` — user auth flows.
- `process_payment.php`, `PriceList.php` — simple payment handling integration.
- `admin/` — all admin management pages (add/edit/delete events, categories, revenue, messages).
- `includes/` — DB connection, header/footer, shared functions.
- `css/`, `js/`, `images/` — static assets and uploaded event images (`uploads/event_images`).

System design (concise)
- Architecture: monolithic PHP app with page-level controllers (no framework). Presentation and basic logic are mixed in the PHP pages with shared includes for common concerns.
- Data: MySQL backend (configure connection in `includes/db_connect.php`).
- Email: PHPMailer wrapper (`Mail.php`) used to send notifications.
- File uploads: event images stored under `uploads/` and `images/event_images/`.

Setup & run (local XAMPP)
1. Place this folder inside `htdocs` (already at `c:/xampp/htdocs/Fresh`).
2. Start Apache and MySQL in XAMPP.
3. Create the database and import the schema (if available). Update DB credentials in `includes/db_connect.php`.
4. Ensure `vendor/` exists. If you need to update dependencies, run `composer install` from the project root.
5. Open in browser: `http://localhost/Fresh`.

Notes & next steps
- No automated tests are included.
- Double-check and harden user inputs (SQL injection, XSS, file upload validation) before deploying publicly.
- To run email features, configure SMTP credentials in `Mail.php` or the included PHPMailer settings.

If you want, I can: add a more detailed architecture diagram, extract common logic into small controllers, or harden security checks next.
