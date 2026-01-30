<?php
/**
 * Workday sync stub (FR12–FR14).
 * This script is intentionally a placeholder: it shows the integration boundary,
 * without embedding any Workday credentials or API assumptions.
 *
 * Two supported modes:
 *  1) CSV import (recommended for a first pilot): map exported Workday fields into profiles.
 *  2) API mode (future): implement fetchWorkdayRecords() using your Workday REST/SOAP endpoints.
 *
 * Usage:
 *   php scripts/workday_sync_stub.php import_csv /path/to/workday_export.csv
 *
 * Expected CSV headers (example):
 *   email,workday_worker_id,first_name,last_name,location_country,location_city,cv_url
 */
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/models.php';

if (php_sapi_name() !== 'cli') { echo "CLI only\n"; exit(1); }

$cmd = $argv[1] ?? '';
if ($cmd === 'import_csv') {
  $path = $argv[2] ?? '';
  if (!$path || !file_exists($path)) { echo "CSV path missing or not found\n"; exit(1); }
  $fh = fopen($path, 'r');
  $headers = fgetcsv($fh);
  if (!$headers) { echo "Empty CSV\n"; exit(1); }
  $headers = array_map('trim', $headers);

  $count = 0;
  while (($row = fgetcsv($fh)) !== false) {
    $data = array_combine($headers, $row);
    $email = strtolower(trim((string)($data['email'] ?? '')));
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

    $u = user_find_by_email($email);
    if (!$u) {
      // Create inactive consultant account so the person can later register with the same email and claim it.
      // In production you might instead create a "pending claim" record.
      $tmpPass = bin2hex(random_bytes(16));
      $uid = user_create($email, $tmpPass, 'consultant');
      admin_set_active($uid, 0);
      profile_ensure_exists($uid);
      $u = user_find($uid);
    }

    $uid = (int)$u['id'];
    profile_update($uid, [
      'first_name' => trim((string)($data['first_name'] ?? '')),
      'last_name' => trim((string)($data['last_name'] ?? '')),
      'location_country' => trim((string)($data['location_country'] ?? '')),
      'location_city' => trim((string)($data['location_city'] ?? '')),
    ]);

    // direct SQL for workday fields not in profile_update allowlist
    $stmt = db()->prepare("UPDATE profiles SET workday_worker_id=?, cv_url=?, updated_at=NOW() WHERE user_id=?");
    $stmt->execute([
      trim((string)($data['workday_worker_id'] ?? '')) ?: null,
      trim((string)($data['cv_url'] ?? '')) ?: null,
      $uid
    ]);

    $count++;
  }
  fclose($fh);
  echo "Imported {$count} rows\n";
  exit(0);
}

echo "Unknown command. Try: import_csv\n";
exit(1);
