# Deployment (Coolify, GitHub push webhook)

Coolify builds `Dockerfile` from this repo and deploys automatically on push:

| Branch | Site |
|---|---|
| `main` | https://portal.himalkom-ipb.com |
| `staging` | https://staging-portal.himalkom-ipb.com |

- The image is nginx + PHP-FPM 8.3 on port 8080. nginx runs only `public/index.php`, and `/storage/` never executes PHP.
- Runtime env vars (APP_KEY, DB_*, ...) are set in Coolify, never in git. Uploads are a persistent volume at `storage/app/public`.
- The container does **not** run migrations. Run `php artisan migrate --force` by hand after review.
- Test changes on `staging` first, then merge into `main`.
- Add `[skip ci]` to a commit message to skip the Coolify deploy.

## Deployment trigger
Pushes to `main` / `staging` are delivered by a GitHub repo webhook (push events, HMAC-signed) to Coolify's
native GitHub webhook endpoint, which builds this Dockerfile and does a health-checked rolling update.
The legacy SSH workflow `.github/workflows/deploy.yml` targeted a terminated host and is disabled through the
GitHub Actions API (state `disabled_manually`); re-enable with `gh workflow enable deploy.yml` if ever needed.
