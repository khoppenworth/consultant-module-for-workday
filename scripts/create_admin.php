<?php
// CLI tool to create an initial admin user.
// Usage (docker):
//   docker compose exec web php scripts/create_admin.php admin@example.org 'StrongPasswordHere'
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/models.php';

if (php_sapi_name() !== 'cli') { echo "CLI only\n"; exit(1); }

$email = $argv[1] ?? null;
$pass  = $argv[2] ?? null;
if (!$email || !$pass) {
  echo "Usage: php scripts/create_admin.php <email> <password>\n";
  exit(1);
}

$email = strtolower(trim((string)$email));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo "Invalid email\n"; exit(1);
}

$existing = user_find_by_email($email);
if ($existing) {
  admin_set_role((int)$existing['id'], 'admin');
  admin_set_active((int)$existing['id'], 1);
  echo "Updated existing user to admin: {$email}\n";
  exit(0);
}

$id = user_create($email, (string)$pass, 'admin');
profile_ensure_exists($id);
echo "Created admin user: {$email} (id={$id})\n";
