<?= $this->include('template/header', ['title' => 'Edit Course']) ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">Edit Course</h5>
        </div>
        <div class="card-body">
          <?php if (isset($validation)): ?>
            <div class="alert alert-danger">
              <?= $validation->listErrors() ?>
            </div>
          <?php endif; ?>

          <form action="<?= base_url('courses/manage/update/' . ($course['id'] ?? '')) ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" name="title" id="title" class="form-control" value="<?= old('title', $course['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" rows="4" class="form-control"><?= old('description', $course['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
              <label for="starting_date" class="form-label">Start Date</label>
              <input type="date" name="starting_date" id="starting_date" class="form-control" value="<?= old('starting_date', $course['starting_date'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
              <label for="end_date" class="form-label">End Date</label>
              <input type="date" name="end_date" id="end_date" class="form-control" value="<?= old('end_date', $course['end_date'] ?? '') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>

            <div class="d-flex justify-content-between">
              <a href="<?= base_url('courses/manage') ?>" class="btn btn-outline-secondary btn-sm">Back to list</a>
              <button type="submit" class="btn btn-success btn-sm">Update Course</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
