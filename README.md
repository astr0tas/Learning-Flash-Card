# Learning Flash Card

Just a website to store flash cards. Cards live inside topics, which can be nested like folders. Users sign in with a regular login form or Google OAuth2.

## Requirements

- PHP >= 8.2 with the `ctype`, `iconv`, `intl`, `pdo_pgsql` extensions
- [Composer](https://getcomposer.org/)
- PostgreSQL (or Docker, to run one via `compose.override.yaml`)
- [Symfony CLI](https://symfony.com/download) (optional, but used below to run the dev server and Tailwind watcher)

No Node/npm is required — frontend assets are served with Symfony AssetMapper.

## Setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

   This also runs `cache:clear`, `assets:install`, and `importmap:install` automatically (see `composer.json`).

2. Copy the environment file and fill in the values (secret, database, mailer, Google OAuth credentials, etc.):

   ```bash
   cp .env .env.local
   ```

   Key variables in `.env.local`:
   - `APP_SECRET` — any random string
   - `DATABASE_URL` — PostgreSQL connection string
   - `MAILER_DSN` — SMTP DSN (use Mailpit locally, see below)
   - `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_API_KEY` — from a Google Cloud OAuth2 app, if you want Google login

3. Start a database (and optionally a local SMTP catcher):

   ```bash
   cp compose.override.example.yaml compose.override.yaml
   docker compose up -d
   ```

4. Run database migrations:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. Build the Tailwind CSS file:

   ```bash
   php bin/console tailwind:build
   ```

## Running the project

```bash
symfony server:start
```

or, without the Symfony CLI:

```bash
php -S localhost:8000 -t public
```

While developing, rebuild Tailwind on file changes in a separate terminal:

```bash
php bin/console tailwind:build --watch
```

Visit `http://localhost:8000`.

## Tests

```bash
php bin/phpunit
```

## Dev container

A ready-to-use setup (app + PostgreSQL) is available under `.devcontainer/` for VS Code / any `devcontainer.json`-compatible editor — open the project and reopen in the container instead of doing the manual setup above.
