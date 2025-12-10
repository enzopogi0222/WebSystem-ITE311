<?= $this->include('template/header', ['title' => 'Course Details']) ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow-sm">
        <div class="card-body">
          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
              <?= esc(session()->getFlashdata('error')) ?>
            </div>
          <?php endif; ?>

          <div class="row mb-4">
            <div class="col-md-8">
              <h3 class="fw-bold mb-2"><?= esc($course['title']) ?></h3>
              <p class="text-muted mb-3"><?= esc($course['description'] ?? 'No description provided.') ?></p>
            </div>
            <div class="col-md-4 text-end">
              <?php if ($isArchived): ?>
                <span class="badge bg-secondary fs-6">Archived</span>
              <?php else: ?>
                <span class="badge bg-success fs-6">Active</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-4">
                <h6 class="text-muted mb-2">Course Code</h6>
                <p class="fs-5 fw-semibold"><?= esc($course['course_code'] ?? 'N/A') ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Year Level</h6>
                <p class="fs-5"><?= esc($course['year_level'] ?? 'N/A') ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Course Type</h6>
                <p class="fs-5"><?= esc($course['course_type'] ?? 'N/A') ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Instructor</h6>
                <p class="fs-5"><?= esc($course['instructor_name'] ?? 'Not Assigned') ?></p>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-4">
                <h6 class="text-muted mb-2">School Year</h6>
                <p class="fs-5"><?= esc($course['school_year'] ?? 'N/A') ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Semester</h6>
                <p class="fs-5"><?= esc($course['semester'] ?? 'N/A') ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Start Date</h6>
                <p class="fs-5"><?= $startingDate ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">End Date</h6>
                <p class="fs-5"><?= $endDate ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">Start Time</h6>
                <p class="fs-5"><?= $startTime ?></p>
              </div>

              <div class="mb-4">
                <h6 class="text-muted mb-2">End Time</h6>
                <p class="fs-5"><?= $endTime ?></p>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-start">
              <a href="<?= base_url('courses/manage') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

