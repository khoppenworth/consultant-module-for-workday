<?php
declare(strict_types=1);

function env(string $key, ?string $default=null): ?string {
  $v = getenv($key);
  if ($v === false || $v === '') return $default;
  return $v;
}

function config(): array {
  return [
    'app_env' => env('APP_ENV', 'prod'),
    'app_url' => env('APP_URL', ''),
    'db' => [
      'host' => env('DB_HOST', '127.0.0.1'),
      'port' => (int)env('DB_PORT', '3306'),
      'name' => env('DB_NAME', 'consultant_db'),
      'user' => env('DB_USER', 'consultant'),
      'pass' => env('DB_PASS', ''),
      'charset' => 'utf8mb4',
    ],
    'session_secret' => env('SESSION_SECRET', 'change_me'),
  ];
}
