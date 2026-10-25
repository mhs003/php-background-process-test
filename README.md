# PHP Background Process Test

A small Laravel app to test whether a long-running Artisan command can be started without blocking the HTTP request that triggers it — similar to firing off a promise in JavaScript and checking on it later.

## What it does

- `GET /` shows the current state of the background job (`idle`, `running`, or `finished`) and its PID.
- `GET /start` launches `php artisan app:generate-text` in the background and immediately redirects back to `/`.
- The command logs a line every second for 25 seconds, then logs `Finished!`. It is defined in `app/Console/Commands/GenerateText.php`.

## How it does it

In `app/Http/Controllers/SiteController.php`:

1. **Starting** — `start()` builds and runs a shell command that `cd`s into the project, runs `exec php artisan app:generate-text` detached from the request, redirects its output to `storage/logs/generate-text-stdout.log`, and writes the child PID to `storage/app/generate-text.pid`. It then returns a redirect right away, so the request is not held open.
2. **Tracking** — the PID file is read back on each request.
3. **Checking** — `isRunning()` uses `posix_kill($pid, 0)` to test whether the process is still alive:
   - no PID file → `idle`
   - signal succeeds → `running`
   - signal fails → `finished`

## Running it

```bash
composer install
php artisan serve
```

Copy `.env.example` to `.env` and set `APP_KEY` if needed (`php artisan key:generate`).

Open the app in a browser, click **Start the job!**, and refresh the page to watch the PID and state change. Progress is visible in `storage/logs/laravel.log`.

## Notes

- Only one job runs at a time; starting another while one is already running just redirects home (a choice for this test, not a limitation).
- This is a proof of concept using `exec()` and POSIX signals, not a queue. It assumes a Unix-like system with the `posix` extension available.
