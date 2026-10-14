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

### Superadmin recovery account

Provision a protected break-glass account against the production database after
the role and permission data has been seeded:

```sh
php artisan db:seed --class=RolePermissionSeeder --force
php artisan swinelocate:ensure-superadmin
```

The command prompts for the account name, email, and password without placing
the password in shell history or deployment configuration. Use a dedicated
email and a unique password of at least 12 characters, then store both securely.
The superadmin role has every configured permission and cannot be assigned,
edited, deactivated, or deleted through normal user/role management. The command
can restore or reset an existing superadmin account after explicit confirmation.
Run it from a trusted shell connected to the intended environment; do not run
production provisioning against a local or staging database.

### Android app

The Android Trusted Web Activity project is in `android/`. It opens the hosted
SwineLocate PWA in a full-screen Android app window without browser controls and
uses the same hosted Laravel application and central database. Its package ID is
`com.swine.locate`; the Cloud host, manifest, and signing certificate are set for
`https://swinelocate-production-h3g41v.laravel.cloud`.

Deploy the `public/.well-known/assetlinks.json` file to Laravel Cloud before
installing or testing the Android app. Android/Chrome verifies that file against
the signing certificate before showing the site without browser controls. If the
certificate or production URL changes, update the Digital Asset Links file and
deploy it before releasing the updated app.

The release keystore (`android/android.keystore`) and its randomly generated
passwords are deliberately excluded from Git. The passwords are stored locally
at `$HOME/.bubblewrap/swine-locate-signing.json`. Back up both the keystore and
passwords securely; without them, you cannot publish updates signed as the same
Android app. Do not commit, email, or publish either signing secret.

With Node.js, Java 17, and the Android SDK installed, build a signed release:

```powershell
$signing = Get-Content "$HOME\.bubblewrap\swine-locate-signing.json" | ConvertFrom-Json
$env:BUBBLEWRAP_KEYSTORE_PASSWORD = $signing.storePassword
$env:BUBBLEWRAP_KEY_PASSWORD = $signing.keyPassword
npm run android:build
Remove-Item Env:BUBBLEWRAP_KEYSTORE_PASSWORD
Remove-Item Env:BUBBLEWRAP_KEY_PASSWORD
```

The signed APK and Play Store app bundle are generated in `android/`. Verify
installation and full-screen domain verification on a real Android device before
distributing the app. A TWA wrapper does not itself provide guaranteed background
synchronization while Android has closed the app; offline recording and sync
continue to use the PWA's existing service worker and IndexedDB queue.

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

Buyers who do not find the swine they want can use the **Find swine nearby**
link on any public farmer profile. The directory lists active swine marked for
sale and links directly to each farmer's public profile, so another QR scan is
not needed. Buyers can share their browser location to sort farms by approximate
distance, or search by municipality/province without sharing device location.
The directory shows only municipality/province as the farm location, not farm
coordinates or street addresses.

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
