# HR System

PHP-based HR system for internal HR workflows, authentication through SSO, SQL Server data storage, and SQLite-backed local cache/log storage.

## Requirements

- PHP 5.6 or newer
- Web server such as Apache/IIS with PHP enabled
- Microsoft SQL Server
- PHP `sqlsrv` extension
- PHP PDO SQLite support
- Network access to the configured SSO service

## Setup

1. Clone the repository into the web server document root.
2. Copy `config.example.php` to `config.php`.
3. Update `config.php` with the local SQL Server, database, credentials, SSO URL, `APPID`, and `SUBDIRECTORY`.
4. Make sure `storage/logs` and `storage/backups` are writable by the web server user.
5. Open the application through the configured web server URL.

For an XAMPP-style local setup where this repository is checked out as `htdocs/hr`, keep:

```php
define('SUBDIRECTORY', 'hr');
```

If the app is deployed at the web root, set:

```php
define('SUBDIRECTORY', '');
```

## Important GitHub Notes

- Do not commit the real `config.php`; it contains environment-specific secrets.
- If `config.php` was already tracked before `.gitignore` was added, remove it from Git tracking without deleting the local file:

```powershell
git rm --cached config.php
```

- Runtime logs and cache files under `storage/logs`, `storage/backups`, and `app/errors.log` are intentionally ignored.
- Third-party assets currently live in the repository under `assets` and `_inc/vendor`. If dependency management is introduced later, document the install command before removing vendored assets.

## Verification

Run PHP syntax checks before pushing changes:

```powershell
php -l config.example.php
php -l _init.php
php -l index.php
```

## License

No license has been declared yet. Add a `LICENSE` file before publishing publicly if redistribution or external contribution is expected.
