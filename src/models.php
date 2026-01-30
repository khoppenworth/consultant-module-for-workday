<?php
declare(strict_types=1);

function user_find_by_email(string $email): ?array {
  $stmt = db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
  $stmt->execute([$email]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function user_find(int $id): ?array {
  $stmt = db()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function user_create(string $email, string $password, string $role='consultant'): int {
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = db()->prepare("INSERT INTO users (email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
  $stmt->execute([$email, $hash, $role]);
  return (int)db()->lastInsertId();
}

function profile_get_by_user(int $user_id): ?array {
  $stmt = db()->prepare("SELECT * FROM profiles WHERE user_id = ? LIMIT 1");
  $stmt->execute([$user_id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

function profile_ensure_exists(int $user_id): void {
  $p = profile_get_by_user($user_id);
  if ($p) return;
  $stmt = db()->prepare("INSERT INTO profiles (user_id, availability_status, updated_at) VALUES (?, 'available_now', NOW())");
  $stmt->execute([$user_id]);
}

function profile_update(int $user_id, array $data): void {
  $allowed = [
    'first_name','last_name','location_country','location_city',
    'availability_status','available_from_date','available_in_months',
    'skills_json','skills_text','recent_experience','linkedin_url'
  ];
  $set = [];
  $params = [];
  foreach ($allowed as $k) {
    if (array_key_exists($k, $data)) {
      $set[] = "$k = ?";
      $params[] = $data[$k];
    }
  }
  $set[] = "updated_at = NOW()";
  $params[] = $user_id;
  $sql = "UPDATE profiles SET " . implode(", ", $set) . " WHERE user_id = ?";
  $stmt = db()->prepare($sql);
  $stmt->execute($params);
}

function recruiter_search(array $filters, int $limit=200): array {
  $where = [];
  $params = [];

  if (!empty($filters['availability_status'])) {
    $where[] = "p.availability_status = ?";
    $params[] = $filters['availability_status'];
  }

  if (!empty($filters['location'])) {
    $where[] = "(p.location_country LIKE ? OR p.location_city LIKE ?)";
    $params[] = '%' . $filters['location'] . '%';
    $params[] = '%' . $filters['location'] . '%';
  }

  if (!empty($filters['skills'])) {
    // Simple keyword match over skills_text and recent_experience
    $where[] = "(p.skills_text LIKE ? OR p.recent_experience LIKE ?)";
    $params[] = '%' . $filters['skills'] . '%';
    $params[] = '%' . $filters['skills'] . '%';
  }

  $sql = "SELECT u.id as user_id, u.email, u.role, p.* 
          FROM users u 
          JOIN profiles p ON p.user_id = u.id
          WHERE u.is_active = 1";
  if ($where) $sql .= " AND " . implode(" AND ", $where);
  $sql .= " ORDER BY p.updated_at DESC LIMIT " . (int)$limit;

  $stmt = db()->prepare($sql);
  $stmt->execute($params);
  return $stmt->fetchAll();
}

function admin_list_users(int $limit=500): array {
  $stmt = db()->prepare("SELECT id, email, role, is_active, created_at, last_login_at FROM users ORDER BY created_at DESC LIMIT " . (int)$limit);
  $stmt->execute();
  return $stmt->fetchAll();
}

function admin_set_role(int $user_id, string $role): void {
  $stmt = db()->prepare("UPDATE users SET role = ? WHERE id = ?");
  $stmt->execute([$role, $user_id]);
}

function admin_set_active(int $user_id, int $is_active): void {
  $stmt = db()->prepare("UPDATE users SET is_active = ? WHERE id = ?");
  $stmt->execute([$is_active, $user_id]);
}
