<?php ob_start(); ?>
<h2>My Profile</h2>
<div class="mb-3">
  <a class="btn btn-primary" href="/profile/edit">Edit</a>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header">Basics</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= h(trim(($profile['first_name'] ?? '').' '.($profile['last_name'] ?? ''))) ?: '—' ?></dd>
          <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= h($user['email']) ?></dd>
          <dt class="col-sm-4">Location</dt><dd class="col-sm-8"><?= h(trim(($profile['location_city'] ?? '').' '.($profile['location_country'] ?? ''))) ?: '—' ?></dd>
          <dt class="col-sm-4">LinkedIn</dt><dd class="col-sm-8"><?= $profile['linkedin_url'] ? '<a href="'.h($profile['linkedin_url']).'" target="_blank">Open</a>' : '—' ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header">Availability</div>
      <div class="card-body">
        <p class="mb-1"><strong>Status:</strong> <?= h($profile['availability_status'] ?? '—') ?></p>
        <?php if (($profile['availability_status'] ?? '') === 'available_from'): ?>
          <p class="mb-0"><strong>Available from:</strong> <?= h($profile['available_from_date'] ?? '') ?: '—' ?></p>
        <?php elseif (($profile['availability_status'] ?? '') === 'unavailable' && !empty($profile['available_in_months'])): ?>
          <p class="mb-0"><strong>Recheck in (months):</strong> <?= h((string)$profile['available_in_months']) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Skills</div>
  <div class="card-body">
    <?php
      $skills = [];
      if (!empty($profile['skills_json'])) $skills = json_decode($profile['skills_json'], true) ?: [];
    ?>
    <?php if ($skills): ?>
      <?php foreach ($skills as $t): ?>
        <span class="badge badge-soft text-dark me-1 mb-1"><?= h($t) ?></span>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted mb-0">—</p>
    <?php endif; ?>
    <?php if (!empty($profile['skills_text'])): ?>
      <hr>
      <p class="mb-0"><?= nl2br(h($profile['skills_text'])) ?></p>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header">Recent experience</div>
  <div class="card-body">
    <p class="mb-0"><?= $profile['recent_experience'] ? nl2br(h($profile['recent_experience'])) : '—' ?></p>
  </div>
</div>

<?php $content = ob_get_clean(); $title='My Profile'; include __DIR__ . '/../layout.php'; ?>
