<?php ob_start(); ?>
<h2>Register</h2>
<form method="post" action="/register" class="mt-3" style="max-width:520px;">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" type="email" name="email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" type="password" name="password" minlength="10" required>
    <div class="form-text">Minimum 10 characters recommended.</div>
  </div>
  <button class="btn btn-warning" type="submit">Create Account</button>
</form>
<?php $content = ob_get_clean(); $title='Register'; include __DIR__ . '/../layout.php'; ?>
