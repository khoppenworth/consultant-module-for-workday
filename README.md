# Consultant Module for Workday

Repository name: `consultant-module-for-workday`

A lightweight **non-Docker** LAMP module (PHP + MySQL) for managing external consultant profiles and enabling recruiter search/export, with a safe boundary for future Workday integration (stub import/export).

## Features

- External consultants: register/login, edit their own profile (availability, location, skills)
- Recruiters: search/filter consultant profiles and export results to CSV
- Admin: manage users, roles, and activation
- Workday sync boundary: import/export stubs to integrate later without coupling the core app to Workday APIs

## Tech stack

- PHP 8.2+
- MySQL 8.0+ (or MariaDB 10.6+)
- Apache 2.4+ (or Nginx)
- Composer (if the project includes PHP dependencies)
- Optional: `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`

---

## Quick start (Ubuntu 22.04/24.04, **no Docker**)

### 1) Install prerequisites

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-mbstring php-xml php-curl unzip
```

Enable Apache rewrite (commonly needed for clean routes):

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2) Create database + user

```bash
sudo mysql
```

Inside MySQL:

```sql
CREATE DATABASE consultant_module CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'consultant_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON consultant_module.* TO 'consultant_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3) Deploy the code to Apache

```bash
sudo mkdir -p /var/www/consultant-module-for-workday
sudo chown -R $USER:$USER /var/www/consultant-module-for-workday
```

Copy this repository into `/var/www/consultant-module-for-workday` (or clone it there).

Set permissions for runtime-writable directories (adjust to match your repo layout if different):

```bash
# If your app uses these folders (common patterns), ensure they're writable:
sudo mkdir -p /var/www/consultant-module-for-workday/storage /var/www/consultant-module-for-workday/uploads /var/www/consultant-module-for-workday/logs
sudo chown -R www-data:www-data /var/www/consultant-module-for-workday/storage /var/www/consultant-module-for-workday/uploads /var/www/consultant-module-for-workday/logs
```

### 4) Configure the application environment

1. Copy the sample env file (if present):
   ```bash
   cd /var/www/consultant-module-for-workday
   cp .env.example .env 2>/dev/null || true
   ```

2. Edit `.env` (or `config/config.php`) with your DB settings:
   - DB_HOST=localhost
   - DB_NAME=consultant_module
   - DB_USER=consultant_app
   - DB_PASS=CHANGE_ME_STRONG_PASSWORD

> If your repo uses a different config mechanism, update the file in `config/` accordingly.

### 5) Initialize the schema

Look for a SQL file in the repo (common locations: `db/`, `database/`, `sql/`, `migrations/`), then run:

```bash
mysql -u consultant_app -p consultant_module < path/to/schema.sql
```

If the repo includes migrations, use the provided migration runner (see `README` sections below or `scripts/`).

### 6) Create Apache VirtualHost

```bash
sudo tee /etc/apache2/sites-available/consultant-module-for-workday.conf >/dev/null <<'CONF'
<VirtualHost *:80>
    ServerName consultant.local
    DocumentRoot /var/www/consultant-module-for-workday/public

    <Directory /var/www/consultant-module-for-workday/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/consultant-module-error.log
    CustomLog ${APACHE_LOG_DIR}/consultant-module-access.log combined
</VirtualHost>
CONF
```

Enable the site:

```bash
sudo a2ensite consultant-module-for-workday.conf
sudo a2dissite 000-default.conf 2>/dev/null || true
sudo systemctl reload apache2
```

Add a local hosts entry (for local testing):

```bash
sudo sh -c 'echo "127.0.0.1 consultant.local" >> /etc/hosts'
```

Open: http://consultant.local

---

## Nginx (optional alternative)

If you prefer Nginx + PHP-FPM, use `php-fpm` and point `root` to `.../public`. Ensure `try_files` routes to `index.php`.

---

## Workday integration (stub boundary)

This repository intentionally keeps Workday coupling minimal:
- Use `workday/` (or `integrations/workday/`) for import/export adapters.
- Start with CSV-based sync for UAT, then replace with Workday APIs when credentials and mapping are available.

Suggested approach:
1. Export consultant profiles -> CSV for Workday staging.
2. Import assignment/position IDs -> update local profiles.
3. Replace CSV steps with API calls once validated.

---

## Security notes (recommended)

- Always set a strong DB password and restrict DB user privileges.
- Store secrets in `.env` and **do not commit** `.env`.
- Use HTTPS in production (Let's Encrypt).
- Enforce least privilege roles (consultant/recruiter/admin).

---

## Troubleshooting

- **Blank page / 500 error**: check Apache logs:
  ```bash
  sudo tail -n 200 /var/log/apache2/consultant-module-error.log
  ```
- **DB connection errors**: verify credentials in `.env` (or `config/`).
- **Permission issues**: ensure writable folders are owned by `www-data`.

---

## License

Add your license here (or keep internal).


---

## Legacy notes from previous README

Repository name: `consultant-module-for-workday`

This is a lightweight, open-source **LAMP** (Linux/Apache/MySQL/PHP) web application that implements the core requirements from the SRS:

- External consultants can **register with email/password** and edit only their own profile (FR1, FR3).  
- Consultants can update **availability**, **location**, and **skills / recent experience** (FR5–FR7).  
- Recruiters can **search** by availability, skills keyword, and location and **export** results to CSV (FR9–FR11).  
- Workday remains the system of record; this app includes a **Workday sync stub** boundary for future API-based sync (FR12–FR14).  

# initialize DB schema
docker compose exec web bash -lc "apt-get update && apt-get install -y default-mysql-client >/dev/null"
docker compose exec web bash -lc "DB_HOST=db DB_PORT=3306 DB_NAME=consultant_db DB_USER=consultant DB_PASS=consultantpass ./scripts/init_db.sh"
# create an admin account
docker compose exec web php scripts/create_admin.php admin@example.org 'ChangeMeToAStrongPassword!'
```

Open: http://localhost:8080

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
