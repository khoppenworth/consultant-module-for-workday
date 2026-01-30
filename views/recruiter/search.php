<?php ob_start(); ?>
<h2>Recruiter Search</h2>

<form method="get" action="/recruiter/search" class="row g-3 align-items-end mt-1">
  <div class="col-md-3">
    <label class="form-label">Availability</label>
    <select class="form-select" name="availability_status">
      <option value="">Any</option>
      <?php
        $opts = ['available_now'=>'Available now','available_from'=>'Available from date','unavailable'=>'Unavailable'];
        foreach ($opts as $k=>$v) {
          $sel = (($filters['availability_status'] ?? '') === $k) ? 'selected' : '';
          echo "<option value='".h($k)."' $sel>".h($v)."</option>";
        }
      ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Location contains</label>
    <input class="form-control" name="location" value="<?= h($filters['location'] ?? '') ?>" placeholder="Country or city">
  </div>
  <div class="col-md-4">
    <label class="form-label">Skills keyword</label>
    <input class="form-control" name="skills" value="<?= h($filters['skills'] ?? '') ?>" placeholder="e.g., surveillance, logistics, DHIS2">
  </div>
  <div class="col-md-2 d-grid">
    <button class="btn btn-primary" type="submit">Search</button>
  </div>
</form>

<div class="mt-3">
  <a class="btn btn-outline-secondary btn-sm" href="/recruiter/export?<?= http_build_query($filters) ?>">Export CSV</a>
</div>

<div class="table-responsive mt-3">
  <table class="table table-sm table-striped align-middle">
    <thead>
      <tr>
        <th>Name</th>
        <th>Location</th>
        <th>Availability</th>
        <th>Skills</th>
        <th>Updated</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $r): ?>
        <?php
          $name = trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')) ?: $r['email'];
          $loc = trim(($r['location_city'] ?? '').' '.($r['location_country'] ?? ''));
          $skills = [];
          if (!empty($r['skills_json'])) $skills = json_decode($r['skills_json'], true) ?: [];
        ?>
        <tr>
          <td><a href="/recruiter/view?id=<?= (int)$r['user_id'] ?>"><?= h($name) ?></a></td>
          <td><?= h($loc) ?></td>
          <td><?= h($r['availability_status'] ?? '') ?></td>
          <td>
            <?php foreach (array_slice($skills, 0, 6) as $t): ?>
              <span class="badge badge-soft text-dark me-1"><?= h($t) ?></span>
            <?php endforeach; ?>
          </td>
          <td><?= h($r['updated_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if (!$results): ?>
  <p class="text-muted">No results.</p>
<?php endif; ?>

<?php $content = ob_get_clean(); $title='Recruiter Search'; include __DIR__ . '/../layout.php'; ?>
