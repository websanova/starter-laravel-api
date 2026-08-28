# Docker Setup

Build and start the containers.

```bash
docker compose up -d --build
```

The container boots but won't serve the app yet. Dependencies aren't installed automatically to avoid modifying `composer.lock` without your say-so. Install them and restart.

```bash
./dev composer install
docker compose restart php
./dev artisan migrate
./dev artisan db:seed
```

Migration creates roles, permissions, and a super user (`super@starter.com` / `initinit`). The super account requires a password change on first login. Seeding adds dev users (`admin@starter.com`, `user@starter.com`, both `testtest`).

On subsequent runs, just start the containers. The entrypoint detects `vendor/` and serves automatically.

```bash
docker compose up -d
```

App runs at `http://localhost:8000`.
