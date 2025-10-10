<?= $this->include('template/header', ['title' => 'Dashboard']) ?>

<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Welcome, <?= isset($user['name']) ? esc($user['name']) : 'User' ?>!</h2>
    <p class="text-muted">
      Your role: 
      <strong class="text-primary">
        <?= isset($user['role']) ? esc(ucfirst($user['role'])) : (isset($role) ? esc(ucfirst($role)) : 'User') ?>
      </strong>
    </p>
  </div>

  <?php 
    $currentRole = isset($user['role']) ? strtolower($user['role']) : strtolower($role ?? 'user'); 
  ?>

  <?php if ($currentRole === 'admin'): ?>
  
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-people-fill me-2"></i> 
        <h5 class="mb-0">All Users</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($users) && is_array($users)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($users as $u): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= esc($u['email'] ?? '') ?></span>
                <span class="badge bg-secondary text-capitalize"><?= esc($u['role'] ?? 'N/A') ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted mb-0">No users to display.</p>
        <?php endif; ?>
      </div>
    </div>

  <?php elseif ($currentRole === 'teacher'): ?>
   
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="bi bi-journal-text me-2"></i>
        <h5 class="mb-0">Your Courses</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($courses) && is_array($courses)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($courses as $c): ?>
              <li class="list-group-item">
                <i class="bi bi-book me-2 text-success"></i><?= esc($c) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-muted mb-0">No courses to display.</p>
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
                <span><i class="bi bi-journal-plus me-2 text-primary"></i><?= esc($course['title']) ?></span>
              <button class="btn btn-primary btn-sm btn-enroll" data-course-id="<?= esc($course['id']) ?>" data-title="<?= esc($course['title']) ?>">Enroll</button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No available courses at the moment.</p>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('.btn-enroll').on('click', function() {
        var courseId = $(this).data('course-id');
        var title = $(this).data('title');
        var button = $(this);

        $.post('/course/enroll', {
            course_id: courseId,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        }, function(response) {
            if (response.success) {
                // Show success message
                alert(response.message);
               
                // Disable button and change text with better styling
                button.prop('disabled', true).text('Enrolled').removeClass('btn-primary').addClass('btn-success');
                
                // Add to enrolled courses list
                var enrolledUl = $('ul.list-group-flush.mb-3');
                if (enrolledUl.length === 0) {
                    // Remove "not enrolled" message if it exists
                    $('.text-muted:contains("You are not enrolled")').remove();
                    // Create enrolled courses list
                    $('.card-body').prepend('<ul class="list-group list-group-flush mb-3"></ul>');
                    enrolledUl = $('ul.list-group-flush.mb-3');
                }
                var currentDate = new Date().toLocaleDateString();
                enrolledUl.append('<li class="list-group-item d-flex justify-content-between align-items-center"><span><i class="bi bi-bookmark-check me-2 text-info"></i>' + title + '</span><small class="text-muted">' + currentDate + '</small></li>');
                
                // Remove course from available courses with animation
                $('#course-' + courseId).fadeOut(300);
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            if (xhr.status === 403) {
                alert('Access denied. Please make sure you are logged in as a student.');
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
