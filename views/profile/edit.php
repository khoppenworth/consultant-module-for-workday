<?php ob_start(); ?>
<h2>Edit Profile</h2>
<form method="post" action="/profile/edit" class="mt-3" style="max-width:900px;">
  <?= csrf_field() ?>
  <div class="row">
    <div class="col-md-6">
      <h5>Basics</h5>
      <div class="mb-3">
        <label class="form-label">First name</label>
        <input class="form-control" name="first_name" value="<?= h($profile['first_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Last name</label>
        <input class="form-control" name="last_name" value="<?= h($profile['last_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Country</label>
        <input class="form-control" name="location_country" value="<?= h($profile['location_country'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">City</label>
        <input class="form-control" name="location_city" value="<?= h($profile['location_city'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">LinkedIn URL</label>
        <input class="form-control" name="linkedin_url" value="<?= h($profile['linkedin_url'] ?? '') ?>" placeholder="https://www.linkedin.com/in/username/">
      </div>
    </div>

    <div class="col-md-6">
      <h5>Availability</h5>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="availability_status">
          <?php
            $cur = $profile['availability_status'] ?? 'available_now';
            $opts = [
              'available_now' => 'Available now',
              'available_from' => 'Available from a specific date',
              'unavailable' => 'Unavailable (recheck later)'
            ];
            foreach ($opts as $k=>$v) {
              $sel = ($k===$cur) ? 'selected' : '';
              echo "<option value='".h($k)."' $sel>".h($v)."</option>";
            }
          ?>
        </select>
        <div class="form-text">Examples include “available now”, “available in X months”, “unavailable”.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Available from (date)</label>
        <input class="form-control" type="date" name="available_from_date" value="<?= h($profile['available_from_date'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">If unavailable: recheck in (months)</label>
        <input class="form-control" type="number" min="0" max="60" name="available_in_months" value="<?= h((string)($profile['available_in_months'] ?? '')) ?>">
      </div>
    </div>
  </div>

  <hr>

  <h5>Skills</h5>
  <div class="mb-3">
    <label class="form-label">Skills tags (comma-separated)</label>
    <input class="form-control" name="skills_tags" value="<?= h($skills_tags ?? '') ?>" placeholder="e.g., Immunization, Data Systems, Supply Chain, Epidemiology">
  </div>
  <div class="mb-3">
    <label class="form-label">Skills and recent experience (free text)</label>
    <textarea class="form-control" rows="6" name="skills_text" placeholder="Add or update skills and recent experience..."><?= h($profile['skills_text'] ?? '') ?></textarea>
  </div>

  <h5>Recent experience details</h5>
  <div class="mb-3">
    <textarea class="form-control" rows="6" name="recent_experience" placeholder="Optional: recent roles, projects, countries, dates..."><?= h($profile['recent_experience'] ?? '') ?></textarea>
  </div>

  <button class="btn btn-primary" type="submit">Save</button>
  <a class="btn btn-outline-secondary" href="/profile">Cancel</a>
</form>
<?php $content = ob_get_clean(); $title='Edit Profile'; include __DIR__ . '/../layout.php'; ?>
