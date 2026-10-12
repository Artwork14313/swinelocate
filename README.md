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
