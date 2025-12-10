<?= $this->include('template/header', ['title' => 'Enrolled Students']) ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">
            <i class="bi bi-people me-2"></i>Enrolled Students - <?= esc($course['title']) ?>
          </h5>
        </div>
        <div class="card-body">
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
              <?= esc(session()->getFlashdata('error')) ?>
            </div>
          <?php endif; ?>

          <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
              <?= esc(session()->getFlashdata('success')) ?>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <p class="text-muted mb-2">
              <strong>Course Code:</strong> <?= esc($course['course_code'] ?? 'N/A') ?> | 
              <strong>Instructor:</strong> <?= esc($course['instructor_name'] ?? 'Not Assigned') ?>
            </p>
          </div>

          <!-- Pending Enrollment Requests -->
          <?php if (!empty($pendingEnrollments) && is_array($pendingEnrollments)): ?>
            <div class="mb-4">
              <h6 class="fw-bold text-warning mb-3">
                <i class="bi bi-clock-history me-2"></i>Pending Enrollment Requests (<?= count($pendingEnrollments) ?>)
              </h6>
              <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                  <thead class="table-warning">
                    <tr>
                      <th>#</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Request Date</th>
                      <th>Request Time</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pendingEnrollments as $index => $request): ?>
                      <?php
                        $requestDate = !empty($request['enrolled_at']) ? date('F d, Y', strtotime($request['enrolled_at'])) : 'N/A';
                        $requestTime = !empty($request['enrolled_at']) ? date('h:i A', strtotime($request['enrolled_at'])) : 'N/A';
                      ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                          <strong><?= esc($request['name'] ?? 'N/A') ?></strong>
                        </td>
                        <td><?= esc($request['email'] ?? 'N/A') ?></td>
                        <td><?= $requestDate ?></td>
                        <td><?= $requestTime ?></td>
                        <td class="text-end">
                          <a href="<?= base_url('courses/manage/approve-enrollment/' . $course['id'] . '/' . $request['id']) ?>" 
                             class="btn btn-sm btn-success me-1"
                             onclick="return confirm('Approve enrollment for <?= esc($request['name']) ?>?');">
                            <i class="bi bi-check-circle me-1"></i> Approve
                          </a>
                          <a href="<?= base_url('courses/manage/reject-enrollment/' . $course['id'] . '/' . $request['id']) ?>" 
                             class="btn btn-sm btn-danger"
                             onclick="return confirm('Reject enrollment request for <?= esc($request['name']) ?>?');">
                            <i class="bi bi-x-circle me-1"></i> Reject
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>

          <!-- Approved Enrolled Students -->
          <h6 class="fw-bold text-success mb-3">
            <i class="bi bi-check-circle me-2"></i>Approved Enrolled Students
          </h6>
          <?php if (!empty($students) && is_array($students)): ?>
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Enrolled Date</th>
                    <th>Enrolled Time</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $index => $student): ?>
                    <?php
                      $enrolledDate = !empty($student['enrolled_at']) ? date('F d, Y', strtotime($student['enrolled_at'])) : 'N/A';
                      $enrolledTime = !empty($student['enrolled_at']) ? date('h:i A', strtotime($student['enrolled_at'])) : 'N/A';
                    ?>
                    <tr>
                      <td><?= $index + 1 ?></td>
                      <td>
                        <strong><?= esc($student['name'] ?? 'N/A') ?></strong>
                      </td>
                      <td><?= esc($student['email'] ?? 'N/A') ?></td>
                      <td><?= $enrolledDate ?></td>
                      <td><?= $enrolledTime ?></td>
                      <td class="text-end">
                        <a href="<?= base_url('courses/manage/remove-student/' . $course['id'] . '/' . $student['user_id']) ?>" 
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Are you sure you want to remove <?= esc($student['name']) ?> from this course?');">
                          <i class="bi bi-trash me-1"></i> Remove
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-3">
              <p class="text-muted">
                <strong>Total Students:</strong> <?= count($students) ?>
              </p>
            </div>
          <?php else: ?>
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i>No students have enrolled in this course yet.
            </div>
          <?php endif; ?>

          <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-end">
              <a href="<?= base_url('courses/manage') ?>" class="btn btn-outline-primary">
                <i class="bi bi-list me-1"></i> Back to Course List
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

