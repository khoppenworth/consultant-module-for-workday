<?php ob_start(); ?>
<h2>Consultant Profile</h2>
<p><a href="/recruiter/search">&larr; Back to search</a></p>

<div class="card mb-3">
  <div class="card-header">Basics</div>
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Name</dt><dd class="col-sm-9"><?= h(trim(($profile['first_name'] ?? '').' '.($profile['last_name'] ?? ''))) ?: '—' ?></dd>
      <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><?= h($user['email'] ?? '—') ?></dd>
      <dt class="col-sm-3">Location</dt><dd class="col-sm-9"><?= h(trim(($profile['location_city'] ?? '').' '.($profile['location_country'] ?? ''))) ?: '—' ?></dd>
      <dt class="col-sm-3">LinkedIn</dt><dd class="col-sm-9"><?= !empty($profile['linkedin_url']) ? '<a href="'.h($profile['linkedin_url']).'" target="_blank">Open</a>' : '—' ?></dd>
    </dl>
  </div>
</div>

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
    <p class="mb-0"><?= !empty($profile['recent_experience']) ? nl2br(h($profile['recent_experience'])) : '—' ?></p>
  </div>
</div>

<?php $content = ob_get_clean(); $title='Consultant Profile'; include __DIR__ . '/../layout.php'; ?>
