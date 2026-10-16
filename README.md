<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Testing on a mobile device

SwineLocate is responsive and can be installed as a PWA. To test it from a phone on
the same local network as the development computer, set `VITE_DEV_SERVER_HOST` in
your local `.env` file to that computer's LAN IP address (without a scheme or port),
then start Laravel and Vite so they listen on all interfaces:

```powershell
# In .env:
VITE_DEV_SERVER_HOST=192.168.1.25

# In separate terminals:
php artisan serve --host=0.0.0.0
npm run dev
```

Open `http://192.168.1.25:8000` on the phone, replacing the example IP with the
development computer's address. When `VITE_DEV_SERVER_HOST` is unset, Vite uses its
default host detection, so desktop development does not depend on a fixed LAN IP.
Camera-based QR scanning requires a secure browser context, such as HTTPS, outside
localhost.

## Production hosting and Android app

For production, deploy SwineLocate to Laravel Cloud and use the HTTPS URL assigned
to the production environment. The local-network IP instructions above are only
for development; phones should use the hosted URL after deployment.
The current production URL is
`https://swinelocate-production-h3g41v.laravel.cloud`.

Laravel Cloud environments use an ephemeral filesystem, so SQLite is not suitable
for production. Attach a managed Laravel MySQL or Serverless Postgres database to
the Cloud environment; Cloud supplies the database connection variables. Do not
commit database credentials or production environment files.

In Laravel Cloud, connect this Git repository, choose the PHP runtime supported
by `composer.json`, and configure the deployment commands:

```sh
# Build command
composer install --no-dev --optimize-autoloader && npm ci && npm run build

# Deploy command
php artisan migrate --force
```

Set `APP_KEY` to a securely generated key (`php artisan key:generate --show`),
`APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` to the assigned HTTPS
address. Review the production database before importing data: select the
authoritative backup, import it securely into the managed database, and verify
record counts and relationships before directing users to the live system. Do
not seed demo users or roles into a production database without reviewing the
seeders and intended accounts first.

### Transactional email (optional)

Laravel Cloud can send application email through its Resend integration. The
Laravel Resend SDK is included in Composer dependencies. Email is not required
for account verification or sign-in. Configure a mail provider only if the
deployed application needs password-reset emails or other mail notifications:

1. Create or use a Resend account and add a sending domain that you control.
   Add the DNS records Resend provides at your domain's DNS provider, then wait
   until the domain is verified.
2. In Laravel Cloud, connect Resend under the organization **Settings →
   Integrations**. Approve the access requested by Cloud.
3. Open the production environment, choose **Add resource → Emails**, select
   the verified sending domain, set the sender address and name, and attach a
   new sending key. Deploy the environment after attaching the resource.
4. Confirm Cloud has injected `RESEND_API_KEY`, `MAIL_MAILER=resend`,
   `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`. Remove any manually configured
   `MAIL_MAILER=log`, since custom variables override the injected mail setting.
   Keep API keys and other credentials in Cloud's secret/environment settings;
   never commit them to the repository.
5. Set `APP_URL` to the production HTTPS URL and redeploy so any mail links use
   the right host. Resend's activity logs show whether a test message was
   accepted, delivered, or rejected.

The local `.env.example` uses `MAIL_MAILER=log` so development does not send
real mail. Configure a mail provider separately for each deployed environment
that needs to send email.

### Superadmin recovery account

Provision a protected break-glass account against the production database after
the role and permission data has been seeded:

```sh
php artisan db:seed --class=RolePermissionSeeder --force
php artisan swinelocate:ensure-superadmin
```

In an interactive terminal, enter a name and email when prompted, then enter
and confirm the password. In a non-interactive cloud command runner, provide
these values as temporary environment variables (mark the password values as
secrets in the runner), then run with `--no-interaction`:

```text
SWINELOCATE_SUPERADMIN_NAME
SWINELOCATE_SUPERADMIN_EMAIL
SWINELOCATE_SUPERADMIN_PASSWORD
SWINELOCATE_SUPERADMIN_PASSWORD_CONFIRM
```

Remove the temporary variables after the command completes. Never put the
password directly in a command argument or committed configuration. Use a
dedicated email and a unique password of at least 12 characters, then store
both securely.
The superadmin role has every configured permission and cannot be assigned,
edited, deactivated, or deleted through normal user/role management. The command
can restore or reset an existing superadmin account after explicit confirmation.
For a non-interactive reset, also set
`SWINELOCATE_SUPERADMIN_CONFIRM_RESET=1`. Run the command from a trusted shell
connected to the intended environment; do not run production provisioning
against a local or staging database.

### Android app

The native Android client is in `android/` and uses the package ID
`com.swine.locate`. It has a native sign-in and field-record interface, a QR
scanner, and a secure token-based connection to the Laravel API. On first launch
or after signing out it opens the public Sagay City farmer map, shown in an
embedded WebView, with a native Login button. A valid saved session restores the
signed-in dashboard. The current client supports permission-filtered record
browsing/search, dashboard summaries, swine QR lookup, and online creation of
swine, health/vaccination, weight, and movement records. Editing existing
records, farm/user/role administration, and offline recording/sync are not yet
implemented in the native client; the website remains available for those
workflows.
The Android map fetches the public `/api/mobile/public/farmers-nearby` endpoint,
which supplies each matching farmer's farm latitude and longitude. It does not
generate sample marker coordinates; missing or out-of-area coordinates are
omitted from the Sagay map.

The API uses Laravel Sanctum bearer tokens. Run `php artisan migrate --force`
when deploying the new API so the personal access token table is created.
Accounts must be active, but email verification is not required: users are
provisioned by an administrator. Public registration and email-verification
routes are disabled. Admin-created accounts are marked verified in the database.
Tokens expire after 30 days, and account deactivation invalidates access on the
next API request. The API returns only modules granted by the user's existing
role permissions.

The Android app's full API base URL, including `/api/mobile`, is set in
`android/app/build.gradle` (`API_BASE_URL`). Change it to the HTTPS API URL for
a different deployment before building. For a local development server, use the
computer's LAN IP and HTTPS; Android's production build deliberately rejects
cleartext HTTP. The app stores its bearer token encrypted using the Android
Keystore.

Install Java 17, Android Studio/SDK (API 36), and the Android SDK build tools.
Build and install a debug APK from PowerShell:

```powershell
cd android
.\gradlew.bat assembleDebug
.\gradlew.bat installDebug
```

The debug APK is written to `android/app/build/outputs/apk/debug/`. A release
signing configuration must be set up with a securely held keystore before
publishing a public APK; do not commit keystore passwords or signing secrets.
For direct APK distribution, place the current signed release at
`public/downloads/swinelocate.apk` and deploy it with the Laravel application.
The public QR/download page is `/android-app`; its QR opens the APK download
directly once that file is present.

## Farmer, swine, and offline workflow

Register farms before adding farmer profiles. A farmer profile stores the farmer's
name, contact number, email, address, and associated farm; each swine registration
must select a farmer from the same farm. A farmer can be linked to multiple swine.
Existing swine records are retained with an unassigned farmer until updated.

Apply the new schema with `php artisan migrate`. The `manage-farmers` permission is
included in `RolePermissionSeeder`; seed role permissions with
`php artisan db:seed --class=RolePermissionSeeder` when setting up or upgrading
the role permissions.

Each farmer has a printable QR code on the farmer profile page. Scanning it opens
a public page without requiring a SwineLocate account. The public page shows the
farmer's name, farm name and general location, plus linked swine identification
details; it does not show farmer contact details, exact addresses, internal notes,
or health records. Keep the deployed application reachable to buyers for QR links
to work.

The public `/public/farmers-nearby` page is a map limited to active farms in
Sagay City (or Sagay), Negros Occidental, with active swine listings marked for
sale. Farm coordinates are used only for map markers; marker popups and the
farmer cards link to each public farmer profile. Farms without coordinates remain
listed but cannot be placed on the map. The public map and profiles show general
location only, not street addresses. The native Android WebView uses a stable
SwineLocate app User-Agent, the official OSM tile URL, and normal HTTP tile
caching in accordance with the OpenStreetMap tile usage policy.

When registering swine offline, the form stores the record and its QR token locally
and queues it for `/swine/sync`. Each local registration carries an idempotency ID,
so retries after an interrupted response do not create duplicate server records.
The sync status page shows records waiting to synchronize or requiring conflict
resolution. Signed-in pages refresh a user-scoped cache of active farms, farmers,
locations, and swine for offline form selection; the cache refreshes when the
device reconnects. Farm, farmer, and location creation or editing remains
online-only. Open the relevant forms and data while online before relying on them
offline; this lookup cache does not make uncached pages available offline.

Health and weight records created offline keep a stable local identifier and
wait in the synchronization queue until a swine registered offline has a
server ID. Health synchronization is idempotent, so retrying a request does
not create a second health record. The queue preserves insertion order; keep
the device's pending registration and dependent records on the same signed-in
account until synchronization completes.

Health and weight records that already exist on the server can also be edited
offline. Open the relevant edit page while online first; offline edits are
saved on the device and queued for the server update endpoint, then retried
when connectivity returns. Server-side validation still applies during sync.

Movement records can transfer a swine between active farms. Choose an active
destination farm, one of its active locations, and a farmer registered to that
farm; the transfer updates the swine's current farm, farmer, and location while
preserving the movement history. Offline transfers are queued with their farm
and farmer details and are subject to the same server-side validation.

The mobile deliverable is an installable, responsive web application/PWA, not
a native mobile app. Offline caching supports previously visited pages and
cached form lookup choices, but QR camera scanning and animal-detail retrieval
are not guaranteed to work offline. Test those flows with the target devices
and network conditions before claiming offline QR capability.
