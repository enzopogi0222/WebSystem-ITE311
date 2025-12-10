<?= $this->include('template/header', ['title' => 'Dashboard']) ?>

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Welcome, <?= isset($user['name']) ? esc($user['name']) : 'User' ?>!</h2>
    <p class="text-muted">
      Your role: 
      <strong class="text-success">
        <?= isset($user['role']) ? esc(ucfirst($user['role'])) : (isset($role) ? esc(ucfirst($role)) : 'User') ?>
      </strong>
    </p>
  </div>

  <?php 
    $currentRole = isset($user['role']) ? strtolower($user['role']) : strtolower($role ?? 'user'); 
  ?>

  <?php if ($currentRole === 'admin'): ?>
  
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="bi bi-people-fill me-2"></i> 
        <h5 class="mb-0">All Users</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($users) && is_array($users)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($users as $u): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= esc($u['email'] ?? '') ?></span>
                <span class="badge bg-success text-capitalize"><?= esc($u['role'] ?? 'N/A') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted mb-0">No users to display.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Materials Management Card for Admin -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="fas fa-folder-open me-2"></i>
        <h5 class="mb-0">Materials Management</h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Manage course materials and uploads</p>

        <?php if (!empty($courses) && is_array($courses)): ?>
          <div class="row">
            <?php foreach ($courses as $course): ?>
              <div class="col-md-4 mb-2">
                <a href="/materials/course/<?= esc($course['id']) ?>" class="btn btn-outline-success btn-sm w-100">
                  <i class="fas fa-book me-2"></i><?= esc($course['title'] ?? 'Course Materials') ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <hr>
          <div class="row">
            <?php foreach ($courses as $course): ?>
              <div class="col-md-4 mb-2">
                <a href="/materials/upload/<?= esc($course['id']) ?>" class="btn btn-success btn-sm w-100">
                  <i class="fas fa-upload me-2"></i>Upload to <?= esc($course['title'] ?? 'Course') ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No courses found. Please add courses first from the admin panel.</p>
        <?php endif; ?>
      </div>
    </div>

  <?php elseif ($currentRole === 'teacher'): ?>
   
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <i class="bi bi-journal-text me-2"></i>
          <h5 class="mb-0">Your Courses</h5>
        </div>
      </div>
      <div class="card-body">
        <?php if (!empty($courses) && is_array($courses)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($courses as $course): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <i class="bi bi-book me-2 text-success"></i>
                  <strong><?= esc($course['title'] ?? 'Course') ?></strong>
                  <?php if (!empty($course['course_code'])): ?>
                    <span class="text-muted ms-2">(<?= esc($course['course_code']) ?>)</span>
                  <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-info">
                    <i class="bi bi-people me-1"></i><?= esc($course['enrolled_count'] ?? 0) ?> Students
                  </span>
                  <?php if (session()->get('role') === 'teacher'): ?>
                  <a href="<?= base_url('courses/manage/students/' . $course['id']) ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-eye me-1"></i>View
                  </a>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted mb-0">No courses to display.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Course Materials Card for Teacher -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="fas fa-folder-open me-2"></i>
        <h5 class="mb-0">Course Materials</h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Manage and upload materials for your courses</p>
        <?php if (!empty($courses) && is_array($courses)): ?>
          <div class="row">
            <?php foreach ($courses as $course): ?>
              <div class="col-md-4 mb-2">
                <a href="/materials/course/<?= esc($course['id']) ?>" class="btn btn-outline-success btn-sm w-100">
                  <i class="fas fa-eye me-2"></i>View <?= esc($course['title'] ?? 'Course') ?> Materials
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <hr>
          <div class="row">
            <?php foreach ($courses as $course): ?>
              <div class="col-md-4 mb-2">
                <a href="/materials/upload/<?= esc($course['id']) ?>" class="btn btn-success btn-sm w-100">
                  <i class="fas fa-upload me-2"></i>Upload to <?= esc($course['title'] ?? 'Course') ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">No courses found. Please contact the administrator to assign or create courses.</p>
        <?php endif; ?>
      </div>
    </div>

  <?php elseif ($currentRole === 'student'): ?>
   
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="bi bi-journal-check me-2"></i>
        <h5 class="mb-0">Your Enrolled Courses</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($enrolledCourses) && is_array($enrolledCourses)): ?>
          <ul class="list-group list-group-flush mb-3">
            <?php foreach ($enrolledCourses as $e): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bookmark-check me-2 text-info"></i><?= esc($e['title'] ?? 'Untitled Course') ?></span>
                <small class="text-muted"><?= esc($e['enrollment_date'] ?? '') ?></small>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted mb-3">You are not enrolled in any courses yet.</p>
        <?php endif; ?>

        <h6 class="fw-bold">Available Courses</h6>
        <?php if (!empty($availableCourses) && is_array($availableCourses)): ?>
          <div id="available-courses" class="list-group">
            <?php foreach ($availableCourses as $course): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center" id="course-<?= $course['id'] ?>">
                <span><i class="bi bi-journal-plus me-2 text-success"></i><?= esc($course['title']) ?></span>
              <button class="btn btn-success btn-sm btn-enroll" data-course-id="<?= esc($course['id']) ?>" data-title="<?= esc($course['title']) ?>">Enroll</button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No available courses at the moment.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Course Materials Card for Student -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="fas fa-download me-2"></i>
        <h5 class="mb-0">Course Materials</h5>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">Access materials from your enrolled courses</p>
        <?php if (!empty($enrolledCourses) && is_array($enrolledCourses)): ?>
          <div class="row">
            <?php foreach ($enrolledCourses as $course): ?>
              <div class="col-md-6 mb-2">
                <a href="/materials/course/<?= esc($course['course_id'] ?? '1') ?>" class="btn btn-outline-success btn-sm w-100">
                  <i class="fas fa-folder-open me-2"></i><?= esc($course['title'] ?? 'Course Materials') ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-3">
            <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">Enroll in courses to access their materials.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php else: ?>
 
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      You are logged in with limited access. Please contact an administrator for full privileges.
    </div>
  <?php endif; ?>
</div>

<script>
window.csrfTokenName = window.csrfTokenName || '<?= csrf_token() ?>';
window.csrfTokenValue = window.csrfTokenValue || '<?= csrf_hash() ?>';

$(document).ready(function() {
    $('.btn-enroll').on('click', function() {
        var courseId = $(this).data('course-id');
        var title = $(this).data('title');
        var button = $(this);

        var postData = {
            course_id: courseId,
        };
        postData[window.csrfTokenName] = window.csrfTokenValue;

        $.post('/course/enroll', postData, function(response) {
         
            if (response && response.csrf_token && response.csrf_hash) {
                window.csrfTokenName = response.csrf_token;
                window.csrfTokenValue = response.csrf_hash;
            }
            if (response.success) {
                // Show success message
                alert(response.message);

                
                button.prop('disabled', true)
                      .text('Enrolled')
                      .removeClass('btn-success')
                      .addClass('btn-secondary');

              
                var enrolledSection = $(
                    'h5.mb-0:contains("Your Enrolled Courses")'
                ).closest('.card').find('.card-body');

                var enrolledUl = enrolledSection.find('ul.list-group-flush.mb-3');
                if (enrolledUl.length === 0) {
                    enrolledSection.find('.text-muted:contains("You are not enrolled")').remove();
                    enrolledSection.prepend('<ul class="list-group list-group-flush mb-3"></ul>');
                    enrolledUl = enrolledSection.find('ul.list-group-flush.mb-3');
                }

                var currentDate = new Date().toLocaleDateString();
                enrolledUl.append(
                    '<li class="list-group-item d-flex justify-content-between align-items-center">'
                    + '<span><i class="bi bi-bookmark-check me-2 text-info"></i>' + title + '</span>'
                    + '<small class="text-muted">' + currentDate + '</small>'
                    + '</li>'
                );

                $('#course-' + courseId).fadeOut(300, function() {
                    $(this).remove();
                });

                // Ask header notifications script to refresh bell count & list
                $(document).trigger('refreshNotifications');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            if (xhr.status === 403) {
                alert('Your session security token has expired or is invalid. The page will reload to refresh it.');
                window.location.reload();
            } else if (xhr.status === 400) {
                alert('Invalid request. Please try again.');
            } else if (xhr.status === 500) {
                alert('Server error. Please contact administrator.');
            } else {
                alert('An error occurred. Please check the console for details and try again.');
            }
        });
    });
});
</script>

</body>
</html>
