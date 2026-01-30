<?php ob_start(); ?>
<h2>Login</h2>
<form method="post" action="/login" class="mt-3" style="max-width:520px;">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" name="email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" type="password" name="password" required>
  </div>
  <button class="btn btn-primary" type="submit">Login</button>
</form>
<p class="mt-3">No account? <a href="/register">Register</a></p>
<?php $content = ob_get_clean(); $title='Login'; include __DIR__ . '/../layout.php'; ?>
