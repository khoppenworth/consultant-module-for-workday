<?php ob_start(); ?>
<div class="p-4 bg-light rounded-3">
  <h1 class="display-6">Talent Availability & Consultant Profile Management</h1>
  <p class="lead mb-0">
    External consultants can register and keep their availability, location, and skills up to date.
    Recruiters can search by availability, skills, and location and export results.
  </p>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/layout.php'; ?>
