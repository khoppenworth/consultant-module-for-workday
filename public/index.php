<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/models.php';

$path = current_path();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function render(string $view, array $vars=[]): void {
  extract($vars);
  include __DIR__ . '/../views/' . $view . '.php';
}

function parse_skill_tags(string $csv): array {
  $parts = array_filter(array_map('trim', explode(',', $csv)));
  $parts = array_values(array_unique(array_filter($parts, fn($x)=>$x!=='')));
  // limit size
  return array_slice($parts, 0, 40);
}

if ($path === '/' && $method === 'GET') {
  render('home');
  exit;
}

// Auth routes
if ($path === '/login' && $method === 'GET') { render('auth/login'); exit; }
if ($path === '/register' && $method === 'GET') { render('auth/register'); exit; }

if ($path === '/login' && $method === 'POST') {
  csrf_verify();
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $password = (string)($_POST['password'] ?? '');
  $u = user_find_by_email($email);
  if (!$u || !(int)$u['is_active'] || !password_verify($password, $u['password_hash'])) {
    flash_set('danger', 'Invalid credentials.');
    redirect('/login');
  }
  auth_login($u);
  // update last_login_at
  $stmt = db()->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
  $stmt->execute([(int)$u['id']]);
  flash_set('success', 'Welcome back.');
  redirect('/profile');
}

if ($path === '/register' && $method === 'POST') {
  csrf_verify();
  $email = strtolower(trim((string)($_POST['email'] ?? '')));
  $password = (string)($_POST['password'] ?? '');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('danger', 'Invalid email.');
    redirect('/register');
  }
  if (strlen($password) < 10) {
    flash_set('danger', 'Password too short.');
    redirect('/register');
  }
  if (user_find_by_email($email)) {
    flash_set('warning', 'Email already registered. Please log in.');
    redirect('/login');
  }
  $id = user_create($email, $password, 'consultant');
  profile_ensure_exists($id);
  auth_login(['id'=>$id,'email'=>$email,'role'=>'consultant']);
  flash_set('success', 'Account created.');
  redirect('/profile/edit');
}

if ($path === '/logout') {
  auth_logout();
  flash_set('success', 'Logged out.');
  redirect('/');
}

// Profile routes
if ($path === '/profile' && $method === 'GET') {
  $user = auth_require();
  profile_ensure_exists((int)$user['id']);
  $profile = profile_get_by_user((int)$user['id']);
  render('profile/view', ['user'=>$user,'profile'=>$profile]);
  exit;
}

if ($path === '/profile/edit' && $method === 'GET') {
  $user = auth_require();
  profile_ensure_exists((int)$user['id']);
  $profile = profile_get_by_user((int)$user['id']);
  $skills = [];
  if (!empty($profile['skills_json'])) $skills = json_decode($profile['skills_json'], true) ?: [];
  $skills_tags = implode(', ', $skills);
  render('profile/edit', ['user'=>$user,'profile'=>$profile,'skills_tags'=>$skills_tags]);
  exit;
}

if ($path === '/profile/edit' && $method === 'POST') {
  $user = auth_require();
  csrf_verify();

  $availability_status = (string)($_POST['availability_status'] ?? 'available_now');
  if (!in_array($availability_status, ['available_now','available_from','unavailable'], true)) $availability_status = 'available_now';

  $available_from_date = trim((string)($_POST['available_from_date'] ?? ''));
  if ($available_from_date === '') $available_from_date = null;

  $available_in_months = trim((string)($_POST['available_in_months'] ?? ''));
  $available_in_months = ($available_in_months === '') ? null : (int)$available_in_months;

  $tags = parse_skill_tags((string)($_POST['skills_tags'] ?? ''));
  $skills_json = json_encode($tags, JSON_UNESCAPED_UNICODE);

  profile_update((int)$user['id'], [
    'first_name' => trim((string)($_POST['first_name'] ?? '')),
    'last_name' => trim((string)($_POST['last_name'] ?? '')),
    'location_country' => trim((string)($_POST['location_country'] ?? '')),
    'location_city' => trim((string)($_POST['location_city'] ?? '')),
    'linkedin_url' => trim((string)($_POST['linkedin_url'] ?? '')),
    'availability_status' => $availability_status,
    'available_from_date' => $available_from_date,
    'available_in_months' => $available_in_months,
    'skills_json' => $skills_json,
    'skills_text' => trim((string)($_POST['skills_text'] ?? '')),
    'recent_experience' => trim((string)($_POST['recent_experience'] ?? '')),
  ]);

  flash_set('success', 'Profile saved.');
  redirect('/profile');
}

// Recruiter routes
if ($path === '/recruiter/search' && $method === 'GET') {
  auth_require_role(['recruiter','admin']);
  $filters = [
    'availability_status' => trim((string)($_GET['availability_status'] ?? '')),
    'location' => trim((string)($_GET['location'] ?? '')),
    'skills' => trim((string)($_GET['skills'] ?? '')),
  ];
  $results = recruiter_search($filters, 200);
  render('recruiter/search', ['filters'=>$filters,'results'=>$results]);
  exit;
}

if ($path === '/recruiter/view' && $method === 'GET') {
  auth_require_role(['recruiter','admin']);
  $id = (int)($_GET['id'] ?? 0);
  $user = user_find($id);
  if (!$user) { http_response_code(404); echo "Not found"; exit; }
  $profile = profile_get_by_user($id);
  render('recruiter/view', ['user'=>$user,'profile'=>$profile]);
  exit;
}

if ($path === '/recruiter/export' && $method === 'GET') {
  auth_require_role(['recruiter','admin']);
  $filters = [
    'availability_status' => trim((string)($_GET['availability_status'] ?? '')),
    'location' => trim((string)($_GET['location'] ?? '')),
    'skills' => trim((string)($_GET['skills'] ?? '')),
  ];
  $results = recruiter_search($filters, 5000);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="consultant_export.csv"');

  $out = fopen('php://output', 'w');
  fputcsv($out, ['user_id','email','first_name','last_name','location_country','location_city','availability_status','available_from_date','available_in_months','skills_tags','skills_text','recent_experience','linkedin_url','updated_at']);
  foreach ($results as $r) {
    $tags = [];
    if (!empty($r['skills_json'])) $tags = json_decode($r['skills_json'], true) ?: [];
    fputcsv($out, [
      $r['user_id'], $r['email'], $r['first_name'], $r['last_name'], $r['location_country'], $r['location_city'],
      $r['availability_status'], $r['available_from_date'], $r['available_in_months'],
      implode('; ', $tags), $r['skills_text'], $r['recent_experience'], $r['linkedin_url'], $r['updated_at']
    ]);
  }
  fclose($out);
  exit;
}

// Admin routes
if ($path === '/admin/users' && $method === 'GET') {
  auth_require_role(['admin']);
  $users = admin_list_users();
  render('admin/users', ['users'=>$users]);
  exit;
}

if ($path === '/admin/user/update' && $method === 'POST') {
  auth_require_role(['admin']);
  csrf_verify();
  $user_id = (int)($_POST['user_id'] ?? 0);
  $role = (string)($_POST['role'] ?? 'consultant');
  $is_active = (int)($_POST['is_active'] ?? 1);
  if (!in_array($role, ['consultant','recruiter','admin'], true)) $role = 'consultant';
  $is_active = $is_active ? 1 : 0;
  admin_set_role($user_id, $role);
  admin_set_active($user_id, $is_active);
  flash_set('success', 'User updated.');
  redirect('/admin/users');
}

// Not found
http_response_code(404);
echo "Not Found";
