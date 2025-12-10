<?= $this->include('template/header', ['title' => 'Manage Courses']) ?>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold">Courses</h2>
    <?php if (session()->get('role') === 'admin'): ?>
      <a href="<?= base_url('courses/manage/create') ?>" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add Course
      </a>
    <?php endif; ?>
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
      <!-- Search Form -->
      <div class="row mb-4">
        <div class="col-md-6">
          <form id="searchForm" class="d-flex" method="get" action="<?= base_url('courses/manage') ?>">
            <div class="input-group">
              <input type="text" id="searchInput" class="form-control" placeholder="Search courses by title, code, or description..." name="search_term" value="<?= esc($searchTerm ?? '') ?>">
              <button class="btn btn-outline-success" type="submit">
                <i class="bi bi-search"></i> Search
              </button>
              <button type="button" id="clearSearchBtn" class="btn btn-outline-secondary" style="<?= empty($searchTerm) ? 'display: none;' : '' ?>">
                <i class="bi bi-x"></i> Clear
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php if (!empty($courses) && is_array($courses)): ?>
        <div class="table-responsive">
          <table id="coursesTable" class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Course Code</th>
                <th>Year Level</th>
                <th>Instructor</th>
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
                  <td><strong><?= esc($course['course_code'] ?? 'N/A') ?></strong></td>
                  <td><?= esc($course['year_level'] ?? 'N/A') ?></td>
                  <td><?= esc($course['instructor_name'] ?? 'Not Assigned') ?></td>
                  <td><?= esc($course['description'] ?? '') ?></td>
                  <td>
                    <?php if ($isArchived): ?>
                      <span class="badge bg-secondary">Archived</span>
                    <?php else: ?>
                      <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= base_url('courses/manage/show/' . $course['id']) ?>" class="btn btn-sm btn-outline-info me-1">
                      <i class="bi bi-eye me-1"></i> View
                    </a>
                    <?php if (session()->get('role') === 'teacher'): ?>
                      <a href="<?= base_url('courses/manage/students/' . $course['id']) ?>" class="btn btn-sm btn-outline-success me-1">
                        <i class="bi bi-people me-1"></i> Students
                      </a>
                    <?php endif; ?>
                    <?php if (session()->get('role') === 'admin'): ?>
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
                    <?php elseif (session()->get('role') === 'teacher'): ?>
                      <a href="<?= base_url('courses/manage/edit/' . $course['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted mb-0">No courses found.<?php if (session()->get('role') === 'admin'): ?> Click "Add Course" to create one.<?php endif; ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Client-side filtering as user types (real-time search)
    $('#searchInput').on('keyup', function () {
        var value = $(this).val().toLowerCase();
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        
        // Filter table rows
        $('#coursesTable tbody tr').each(function () {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show message if no results
        var visibleRows = $('#coursesTable tbody tr:visible').length;
        if (value.length > 0 && visibleRows === 0) {
            if ($('#noResultsMessage').length === 0) {
                $('#coursesTable tbody').append(
                    '<tr id="noResultsMessage"><td colspan="8" class="text-center text-muted py-4">No courses match your search.</td></tr>'
                );
            }
        } else {
            $('#noResultsMessage').remove();
        }
    });

    // Server-side search with AJAX (on form submit)
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        var searchTerm = $('#searchInput').val();
        var url = '<?= base_url('courses/manage') ?>';
        
        // Show loading state
        var $submitBtn = $(this).find('button[type="submit"]');
        var originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Searching...');
        
        // Make AJAX request
        $.ajax({
            url: url,
            type: 'GET',
            data: { search_term: searchTerm },
            dataType: 'json',
            success: function(data) {
                // Reload page with search results
                window.location.href = url + (searchTerm ? '?search_term=' + encodeURIComponent(searchTerm) : '');
            },
            error: function() {
                // On error, still reload page
                window.location.href = url + (searchTerm ? '?search_term=' + encodeURIComponent(searchTerm) : '');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Clear search button functionality
    $('#clearSearchBtn').on('click', function () {
        $('#searchInput').val('');
        $('#clearSearchBtn').hide();
        $('#coursesTable tbody tr').show();
        $('#noResultsMessage').remove();
        
        // Optionally reload page to clear server-side search
        window.location.href = '<?= base_url('courses/manage') ?>';
    });
});
</script>

</body>
</html>