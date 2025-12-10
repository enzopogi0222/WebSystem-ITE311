<?= $this->include('template/header', ['title' => 'Add Course']) ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">Add New Course</h5>
        </div>
        <div class="card-body">
          <?php if (isset($validation)): ?>
            <div class="alert alert-danger">
              <?= $validation->listErrors() ?>
            </div>
          <?php endif; ?>

          <form action="<?= base_url('courses/manage/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" name="title" id="title" class="form-control" value="<?= old('title') ?>" required>
            </div>

            <div class="mb-3">
              <label for="course_code" class="form-label">Course Code</label>
              <input type="text" name="course_code" id="course_code" class="form-control" value="<?= old('course_code') ?>" required placeholder="e.g., CS101, MATH201">
            </div>

            <div class="mb-3">
              <label for="year_level" class="form-label">Year Level</label>
              <select name="year_level" id="year_level" class="form-control">
                <option value="">Select Year Level</option>
                <option value="1st Year" <?= old('year_level') === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                <option value="2nd Year" <?= old('year_level') === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                <option value="3rd Year" <?= old('year_level') === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                <option value="4th Year" <?= old('year_level') === '4th Year' ? 'selected' : '' ?>>4th Year</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="instructor_id" class="form-label">Instructor</label>
              <select name="instructor_id" id="instructor_id" class="form-control">
                <option value="">Select Instructor</option>
                <?php if (isset($teachers) && !empty($teachers)): ?>
                  <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= $teacher['id'] ?>" <?= old('instructor_id') == $teacher['id'] ? 'selected' : '' ?>>
                      <?= esc($teacher['name']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea name="description" id="description" rows="4" class="form-control"><?= old('description') ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="school_year" class="form-label">School Year</label>
                  <input type="text" name="school_year" id="school_year" class="form-control" value="<?= old('school_year') ?>" placeholder="e.g., 2024-2025">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="semester" class="form-label">Semester</label>
                  <select name="semester" id="semester" class="form-control">
                    <option value="">Select Semester</option>
                    <option value="1st Semester" <?= old('semester') === '1st Semester' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2nd Semester" <?= old('semester') === '2nd Semester' ? 'selected' : '' ?>>2nd Semester</option>
                    <option value="Summer" <?= old('semester') === 'Summer' ? 'selected' : '' ?>>Summer</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="course_type" class="form-label">Course Type</label>
              <select name="course_type" id="course_type" class="form-control">
                <option value="">Select Course Type</option>
                <option value="Major" <?= old('course_type') === 'Major' ? 'selected' : '' ?>>Major</option>
                <option value="Minor" <?= old('course_type') === 'Minor' ? 'selected' : '' ?>>Minor</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="starting_date" class="form-label">Start Date</label>
              <input type="date" name="starting_date" id="starting_date" class="form-control" value="<?= old('starting_date') ?>" min="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
              <label for="end_date" class="form-label">End Date <span id="end_date_required" style="display:none; color:red;">*</span></label>
              <input type="date" name="end_date" id="end_date" class="form-control" value="<?= old('end_date') ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="start_time" class="form-label">Start Time</label>
                  <input type="time" name="start_time" id="start_time" class="form-control" value="<?= old('start_time') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="end_time" class="form-label">End Time</label>
                  <input type="time" name="end_time" id="end_time" class="form-control" value="<?= old('end_time') ?>">
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between">
              <a href="<?= base_url('courses/manage') ?>" class="btn btn-outline-secondary btn-sm">Back to list</a>
              <button type="submit" class="btn btn-success btn-sm">Save Course</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const semesterSelect = document.getElementById('semester');
    const schoolYearInput = document.getElementById('school_year');
    const startDateInput = document.getElementById('starting_date');
    const endDateInput = document.getElementById('end_date');
    const endDateRequired = document.getElementById('end_date_required');
    
    // Track if dates were manually edited
    let datesManuallyEdited = false;
    
    function getYearFromSchoolYear() {
        const schoolYear = schoolYearInput.value.trim();
        if (schoolYear) {
            // Extract year from formats like "2024-2025" or "2024"
            const match = schoolYear.match(/(\d{4})/);
            if (match) {
                return parseInt(match[1]);
            }
        }
        return new Date().getFullYear();
    }
    
    function populateDates() {
        if (datesManuallyEdited) return; // Don't auto-populate if user manually edited
        
        const semester = semesterSelect.value;
        if (!semester) return;
        
        const year = getYearFromSchoolYear();
        let startDate = '';
        let endDate = '';
        
        if (semester === '1st Semester') {
            // 1st Semester: August to December
            startDate = year + '-08-01';
            endDate = year + '-12-31';
        } else if (semester === '2nd Semester') {
            // 2nd Semester: January to May (use next year if school year format is YYYY-YYYY)
            const schoolYear = schoolYearInput.value.trim();
            const yearMatch = schoolYear.match(/(\d{4})-(\d{4})/);
            const secondYear = yearMatch ? parseInt(yearMatch[2]) : year;
            startDate = secondYear + '-01-15';
            endDate = secondYear + '-05-31';
        } else if (semester === 'Summer') {
            // Summer: June to July
            startDate = year + '-06-01';
            endDate = year + '-07-31';
        }
        
        if (startDate && endDate) {
            // Only populate if fields are empty
            if (!startDateInput.value) {
                startDateInput.value = startDate;
            }
            if (!endDateInput.value) {
                endDateInput.value = endDate;
            }
        }
    }
    
    function toggleEndDateRequired() {
        if (semesterSelect.value !== '') {
            endDateInput.setAttribute('required', 'required');
            endDateRequired.style.display = 'inline';
            populateDates();
        } else {
            endDateInput.removeAttribute('required');
            endDateRequired.style.display = 'none';
        }
    }
    
    // Allow manual editing to override auto-populated dates
    startDateInput.addEventListener('change', function() {
        datesManuallyEdited = true;
    });
    
    endDateInput.addEventListener('change', function() {
        datesManuallyEdited = true;
    });
    
    semesterSelect.addEventListener('change', function() {
        datesManuallyEdited = false; // Reset when semester changes
        toggleEndDateRequired();
    });
    
    schoolYearInput.addEventListener('input', function() {
        if (semesterSelect.value !== '' && !datesManuallyEdited) {
            populateDates();
        }
    });
    
    toggleEndDateRequired(); // Check on page load
});
</script>
