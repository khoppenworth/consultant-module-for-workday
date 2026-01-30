<?php ob_start(); ?>
<h2>Admin: Users</h2>
<p class="text-muted">Promote users to recruiter/admin and activate/deactivate accounts.</p>

<div class="table-responsive">
  <table class="table table-sm table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Role</th>
        <th>Active</th>
        <th>Created</th>
        <th>Last login</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $r): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= h($r['email']) ?></td>
          <td><?= h($r['role']) ?></td>
          <td><?= (int)$r['is_active'] ? 'Yes' : 'No' ?></td>
          <td><?= h($r['created_at'] ?? '') ?></td>
          <td><?= h($r['last_login_at'] ?? '') ?></td>
          <td>
            <form method="post" action="/admin/user/update" class="d-flex gap-2">
              <?= csrf_field() ?>
              <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>">
              <select class="form-select form-select-sm" name="role" style="width:160px;">
                <?php foreach (['consultant','recruiter','admin'] as $role): ?>
                  <option value="<?= h($role) ?>" <?= $role===$r['role']?'selected':'' ?>><?= h($role) ?></option>
                <?php endforeach; ?>
              </select>
              <select class="form-select form-select-sm" name="is_active" style="width:120px;">
                <option value="1" <?= (int)$r['is_active']===1?'selected':'' ?>>active</option>
                <option value="0" <?= (int)$r['is_active']===0?'selected':'' ?>>inactive</option>
              </select>
              <button class="btn btn-sm btn-primary" type="submit">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php $content = ob_get_clean(); $title='Admin Users'; include __DIR__ . '/../layout.php'; ?>
