# Consultant Module for Workday

Repository name: `consultant-module-for-workday`

This is a lightweight, open-source **LAMP** (Linux/Apache/MySQL/PHP) web application that implements the core requirements from the SRS:

- External consultants can **register with email/password** and edit only their own profile (FR1, FR3).  
- Consultants can update **availability**, **location**, and **skills / recent experience** (FR5–FR7).  
- Recruiters can **search** by availability, skills keyword, and location and **export** results to CSV (FR9–FR11).  
- Workday remains the system of record; this app includes a **Workday sync stub** boundary for future API-based sync (FR12–FR14).  

## Quick start (Docker)

Prereqs: Docker + Docker Compose.

1. Build and start containers.

```bash
docker compose up -d --build
```

2. Initialize the database schema (wait a few seconds if MySQL is still starting).

```bash
docker compose exec web bash -lc "apt-get update && apt-get install -y default-mysql-client >/dev/null"
docker compose exec web bash -lc "DB_HOST=db DB_PORT=3306 DB_NAME=consultant_db DB_USER=consultant DB_PASS=consultantpass ./scripts/init_db.sh"
```

3. Create an admin account.

```bash
docker compose exec web php scripts/create_admin.php admin@example.org 'ChangeMeToAStrongPassword!'
```

Open: http://localhost:8080

Notes:
- Override defaults by editing `docker-compose.yml` or setting environment variables in a `.env` file (Compose reads it automatically).
- If you need to reset the database, run `docker compose down -v` to remove the MySQL volume, then repeat the steps above.

## Manual install (non-Docker)

1. Install Apache, PHP 8.2+, and MySQL 8.
2. Enable Apache rewrite module and allow `.htaccess` in your vhost configuration.
3. Create a MySQL database and user.
4. Apply the schema:

```bash
mysql -h <host> -u <user> -p <db_name> < db/schema.sql
```

5. Set environment variables (Apache vhost or `.env` managed by your platform):

- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `APP_URL` (base URL for links and redirects)
- `SESSION_SECRET` (set to a long random value)

## Roles

- `consultant`: default for self-registered users.
- `recruiter`: can search/export and view consultant profiles.
- `admin`: can manage users/roles and activate/deactivate accounts.

## Security notes (production)

- Terminate TLS at a load balancer or configure HTTPS on Apache (NFR2).
- Set a strong `SESSION_SECRET`.
- Restrict `/scripts` access at the web server level (they are intended for CLI only).

## Workday integration boundary (stub)

See `scripts/workday_sync_stub.php` for a safe placeholder that supports **CSV import** from Workday exports today, and leaves the **API fetch** implementation for later (FR12–FR14).

## Project structure

- `public/` – Apache document root (front controller: `index.php`)
- `src/` – config/db/auth/csrf helpers + models
- `views/` – Bootstrap-based templates
- `db/` – schema
- `scripts/` – init + admin creation + Workday sync stub

## License

MIT (see `LICENSE`).
