<?php
declare(strict_types=1);

function auth_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function auth_require(): array {
  $u = auth_user();
  if (!$u) {
    flash_set('warning', 'Please log in.');
    redirect('/login');
  }
  return $u;
}

function auth_require_role(array $roles): array {
  $u = auth_require();
  if (!in_array($u['role'], $roles, true)) {
    http_response_code(403);
    echo "Forbidden.";
    exit;
  }
  return $u;
}

function auth_login(array $user): void {
  // Regenerate session ID on login
  session_regenerate_id(true);
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'email' => (string)$user['email'],
    'role' => (string)$user['role'],
  ];
}

function auth_logout(): void {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}
