<?php $u = auth_user(); $flash = flash_get(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'Consultant Availability DB') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Consultant DB</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($u): ?>
          <li class="nav-item"><a class="nav-link" href="/profile">My Profile</a></li>
          <?php if (in_array($u['role'], ['recruiter','admin'], true)): ?>
            <li class="nav-item"><a class="nav-link" href="/recruiter/search">Recruiter Search</a></li>
          <?php endif; ?>
          <?php if ($u['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/admin/users">Admin</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if ($u): ?>
          <li class="nav-item"><span class="navbar-text me-3"><?= h($u['email']) ?> (<?= h($u['role']) ?>)</span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/logout">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm me-2" href="/login">Login</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm" href="/register">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container">
  <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?> mt-3"><?= h($flash['message']) ?></div>
  <?php endif; ?>
  <?= $content ?>
  <hr class="my-4">
  <p class="text-muted small">Prototype LAMP app. Ensure HTTPS in production.</p>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
