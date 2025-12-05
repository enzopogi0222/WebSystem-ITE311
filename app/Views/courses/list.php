<?= $this->include('template/header', ['title' => 'Manage Courses']) ?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold">Courses</h2>
    <a href="<?= base_url('courses/manage/create') ?>" class="btn btn-success btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Add Course
    </a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0">Course List</h5>
    </div>
    <div class="card-body">
      <?php if (!empty($courses) && is_array($courses)): ?>
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($courses as $index => $course): ?>
                <?php
                  // Determine archived/expired status:
                  // - explicitly archived via is_archive flag, OR
                  // - end_date is today or earlier
                  $today = date('Y-m-d');
                  $isFlagArchived = !empty($course['is_archive']) && (int) $course['is_archive'] === 1;
                  $endDateRaw = $course['end_date'] ?? null;
                  $endDateDateOnly = $endDateRaw ? substr($endDateRaw, 0, 10) : null;
                  // Treat "0000-00-00" as no end date (active), not expired
                  $isDateExpired = !empty($endDateDateOnly)
                                   && $endDateDateOnly !== '0000-00-00'
                                   && $endDateDateOnly <= $today;
                  $isArchived = $isFlagArchived || $isDateExpired;
                ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= esc($course['title'] ?? '') ?></td>
                  <td><?= esc($course['description'] ?? '') ?></td>
                  <td>
                    <?php if ($isArchived): ?>
                      <span class="badge bg-secondary">Archived</span>
                    <?php else: ?>
                      <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= base_url('courses/manage/edit/' . $course['id']) ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                    <?php if (! $isArchived): ?>
                      <a href="<?= base_url('courses/manage/archive/' . $course['id']) ?>" class="btn btn-sm btn-outline-warning me-1"
                         onclick="return confirm('Archive this course? Students will no longer see it.');">
                        Archive
                      </a>
                    <?php else: ?>
                      <a href="<?= base_url('courses/manage/restore/' . $course['id']) ?>" class="btn btn-sm btn-outline-secondary me-1"
                         onclick="return confirm('Restore this course? Students will see it again if it is available.');">
                        Restore
                      </a>
                    <?php endif; ?>
                    <a href="<?= base_url('courses/manage/delete/' . $course['id']) ?>" class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Are you sure you want to delete this course?');">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted mb-0">No courses found. Click "Add Course" to create one.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
