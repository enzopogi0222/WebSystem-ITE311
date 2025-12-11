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
  
    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-people-fill text-primary fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['total_users']) ? $stats['total_users'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Total Users</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-person-badge text-warning fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['total_teachers']) ? $stats['total_teachers'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Teachers</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-info bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-person text-info fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['total_students']) ? $stats['total_students'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Students</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-success bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-book text-success fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['total_courses']) ? $stats['total_courses'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Total Courses</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-success bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-check-circle text-success fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['active_courses']) ? $stats['active_courses'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Active Courses</p>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 mb-3">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                <i class="bi bi-shield-check text-primary fs-4"></i>
              </div>
            </div>
            <h3 class="mb-0 fw-bold"><?= isset($stats['total_admins']) ? $stats['total_admins'] : 0 ?></h3>
            <p class="text-muted mb-0 small">Administrators</p>
          </div>
        </div>
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
        
        <!-- Search Bar for Materials -->
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="materialsSearchInput" class="form-control" placeholder="Search courses by title or code...">
            <button class="btn btn-outline-secondary" type="button" id="clearMaterialsSearch">
              <i class="bi bi-x"></i> Clear
            </button>
          </div>
        </div>

        <?php if (!empty($courses) && is_array($courses)): ?>
          <div id="materialsSection">
            <div class="row" id="viewMaterialsRow">
              <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-2 course-item" 
                     data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                     data-code="<?= strtolower(esc($course['course_code'] ?? '')) ?>">
                  <a href="/materials/course/<?= esc($course['id']) ?>" class="btn btn-outline-success btn-sm w-100">
                    <i class="fas fa-book me-2"></i><?= esc($course['title'] ?? 'Course Materials') ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <hr>
            <div class="row" id="uploadMaterialsRow">
              <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-2 course-item" 
                     data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                     data-code="<?= strtolower(esc($course['course_code'] ?? '')) ?>">
                  <a href="/materials/upload/<?= esc($course['id']) ?>" class="btn btn-success btn-sm w-100">
                    <i class="fas fa-upload me-2"></i>Upload to <?= esc($course['title'] ?? 'Course') ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <div id="noMaterialsResults" class="text-center text-muted py-3" style="display: none;">
              <i class="bi bi-search"></i> No courses found matching your search.
            </div>
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
          <!-- Search Bar for Your Courses -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="teacherCoursesSearchInput" class="form-control" placeholder="Search courses by title or code...">
              <button class="btn btn-outline-secondary" type="button" id="clearTeacherCoursesSearch">
                <i class="bi bi-x"></i> Clear
              </button>
            </div>
          </div>
          
          <ul class="list-group list-group-flush" id="teacherCoursesList">
            <?php foreach ($courses as $course): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center teacher-course-item" 
                  data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                  data-code="<?= strtolower(esc($course['course_code'] ?? '')) ?>">
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
          <div id="noTeacherCoursesResults" class="text-center text-muted py-3" style="display: none;">
            <i class="bi bi-search"></i> No courses found matching your search.
          </div>
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
        
        <!-- Search Bar for Teacher Materials -->
        <?php if (!empty($courses) && is_array($courses)): ?>
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="teacherMaterialsSearchInput" class="form-control" placeholder="Search courses by title or code...">
            <button class="btn btn-outline-secondary" type="button" id="clearTeacherMaterialsSearch">
              <i class="bi bi-x"></i> Clear
            </button>
          </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($courses) && is_array($courses)): ?>
          <div id="teacherMaterialsSection">
            <div class="row" id="teacherViewMaterialsRow">
              <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-2 teacher-material-item" 
                     data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                     data-code="<?= strtolower(esc($course['course_code'] ?? '')) ?>">
                  <a href="/materials/course/<?= esc($course['id']) ?>" class="btn btn-outline-success btn-sm w-100">
                    <i class="fas fa-eye me-2"></i>View <?= esc($course['title'] ?? 'Course') ?> Materials
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <hr>
            <div class="row" id="teacherUploadMaterialsRow">
              <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-2 teacher-material-item" 
                     data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                     data-code="<?= strtolower(esc($course['course_code'] ?? '')) ?>">
                  <a href="/materials/upload/<?= esc($course['id']) ?>" class="btn btn-success btn-sm w-100">
                    <i class="fas fa-upload me-2"></i>Upload to <?= esc($course['title'] ?? 'Course') ?>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
            <div id="noTeacherMaterialsResults" class="text-center text-muted py-3" style="display: none;">
              <i class="bi bi-search"></i> No courses found matching your search.
            </div>
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
          <!-- Search Bar for Enrolled Courses -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="enrolledCoursesSearchInput" class="form-control" placeholder="Search enrolled courses by title or date...">
              <button class="btn btn-outline-secondary" type="button" id="clearEnrolledCoursesSearch">
                <i class="bi bi-x"></i> Clear
              </button>
            </div>
          </div>
          
          <ul class="list-group list-group-flush mb-3" id="enrolledCoursesList">
            <?php foreach ($enrolledCourses as $e): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center enrolled-course-item" 
                  data-title="<?= strtolower(esc($e['title'] ?? '')) ?>"
                  data-date="<?= strtolower(esc($e['enrollment_date'] ?? '')) ?>">
                <div class="d-flex align-items-center">
                  <i class="bi bi-bookmark-check me-2 text-info"></i>
                  <span><?= esc($e['title'] ?? 'Untitled Course') ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted"><?= esc($e['enrollment_date'] ?? '') ?></small>
                  <a href="<?= base_url('/courses/manage/show/' . ($e['course_id'] ?? $e['id'] ?? '')) ?>" 
                     class="btn btn-sm btn-outline-success" 
                     title="View Course Details">
                    <i class="bi bi-eye me-1"></i>View
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div id="noEnrolledCoursesResults" class="text-center text-muted py-3" style="display: none;">
            <i class="bi bi-search"></i> No enrolled courses found matching your search.
          </div>
        <?php else: ?>
          <p class="text-muted mb-3">You are not enrolled in any courses yet.</p>
        <?php endif; ?>

        <h6 class="fw-bold">Available Courses</h6>
        
        <?php if (!empty($availableCourses) && is_array($availableCourses)): ?>
          <!-- Search Bar for Available Courses -->
          <div class="mb-3">
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" id="availableCoursesSearchInput" class="form-control" placeholder="Search available courses by title...">
              <button class="btn btn-outline-secondary" type="button" id="clearAvailableCoursesSearch">
                <i class="bi bi-x"></i> Clear
              </button>
            </div>
          </div>
          
          <div id="available-courses" class="list-group">
            <?php foreach ($availableCourses as $course): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center available-course-item" 
                   id="course-<?= $course['id'] ?>"
                   data-title="<?= strtolower(esc($course['title'] ?? '')) ?>"
                   style="display: flex;">
                <span><i class="bi bi-journal-plus me-2 text-success"></i><?= esc($course['title']) ?></span>
                <button class="btn btn-success btn-sm btn-enroll" data-course-id="<?= esc($course['id']) ?>" data-title="<?= esc($course['title']) ?>">Enroll</button>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="noAvailableCoursesResults" class="text-center text-muted py-3" style="display: none;">
            <i class="bi bi-search"></i> No available courses found matching your search.
          </div>
        <?php else: ?>
          <p class="text-muted" id="noAvailableCoursesMessage">No available courses at the moment.</p>
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
        
        <!-- Search Bar for Course Materials -->
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="studentMaterialsSearchInput" class="form-control" placeholder="Search course materials...">
            <button class="btn btn-outline-secondary" type="button" id="clearStudentMaterialsSearch">
              <i class="bi bi-x"></i> Clear
            </button>
          </div>
        </div>
        
        <?php if (!empty($enrolledCourses) && is_array($enrolledCourses)): ?>
          <div class="row" id="studentMaterialsRow">
            <?php foreach ($enrolledCourses as $course): ?>
              <div class="col-md-6 mb-2 student-material-item" 
                   data-title="<?= strtolower(esc($course['title'] ?? '')) ?>">
                <a href="/materials/course/<?= esc($course['course_id'] ?? '1') ?>" class="btn btn-outline-success btn-sm w-100">
                  <i class="fas fa-folder-open me-2"></i><?= esc($course['title'] ?? 'Course Materials') ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <div id="noStudentMaterialsResults" class="text-center text-muted py-3" style="display: none;">
            <i class="bi bi-search"></i> No course materials found matching your search.
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
    // Materials Search Functionality (Admin)
    $('#materialsSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase().trim();
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearMaterialsSearch').show();
        } else {
            $('#clearMaterialsSearch').hide();
            $('#noMaterialsResults').hide();
            $('.course-item').show();
            return;
        }
        
        // Filter course items - check entire text content
        $('.course-item').each(function() {
            var itemText = $(this).text().toLowerCase();
            if (itemText.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show message if no results
        var visibleItems = $('.course-item:visible').length;
        if (visibleItems === 0) {
            $('#noMaterialsResults').show();
        } else {
            $('#noMaterialsResults').hide();
        }
    });
    
    $('#clearMaterialsSearch').on('click', function() {
        $('#materialsSearchInput').val('');
        $('.course-item').show();
        $('#noMaterialsResults').hide();
        $(this).hide();
    });
    
    // Student: Course Materials Search
    $('#studentMaterialsSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase().trim();
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearStudentMaterialsSearch').show();
        } else {
            $('#clearStudentMaterialsSearch').hide();
            $('#noStudentMaterialsResults').hide();
            $('.student-material-item').show();
            return;
        }
        
        // Filter material items - check entire text content
        $('.student-material-item').each(function() {
            var itemText = $(this).text().toLowerCase();
            if (itemText.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show message if no results
        var visibleItems = $('.student-material-item:visible').length;
        if (visibleItems === 0) {
            $('#noStudentMaterialsResults').show();
        } else {
            $('#noStudentMaterialsResults').hide();
        }
    });
    
    $('#clearStudentMaterialsSearch').on('click', function() {
        $('#studentMaterialsSearchInput').val('');
        $('.student-material-item').show();
        $('#noStudentMaterialsResults').hide();
        $(this).hide();
    });
    
    // Teacher: Materials Search
    function filterTeacherMaterials() {
        var value = $('#teacherMaterialsSearchInput').val().toLowerCase().trim();
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearTeacherMaterialsSearch').show();
        } else {
            $('#clearTeacherMaterialsSearch').hide();
            $('#noTeacherMaterialsResults').hide();
            $('.teacher-material-item').show();
            return;
        }
        
        // Filter material items - check entire text content
        $('.teacher-material-item').each(function() {
            var itemText = $(this).text().toLowerCase();
            if (itemText.indexOf(value) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Show message if no results
        var visibleItems = $('.teacher-material-item:visible').length;
        if (visibleItems === 0) {
            $('#noTeacherMaterialsResults').show();
        } else {
            $('#noTeacherMaterialsResults').hide();
        }
    }
    
    $('#teacherMaterialsSearchInput').on('keyup input', filterTeacherMaterials);
    
    $('#clearTeacherMaterialsSearch').on('click', function() {
        $('#teacherMaterialsSearchInput').val('');
        $('.teacher-material-item').show();
        $('#noTeacherMaterialsResults').hide();
        $(this).hide();
    });
    
    // Teacher: Your Courses Search
    $('#teacherCoursesSearchInput').on('keyup input paste', function() {
        var value = $(this).val().toLowerCase().trim();
        var hasResults = false;
        var $items = $('.teacher-course-item');
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearTeacherCoursesSearch').show();
            
            // Filter course items - only show matching items, explicitly hide others
            $items.each(function() {
                var $item = $(this);
                var itemText = $item.text().toLowerCase();
                
                if (itemText.indexOf(value) > -1) {
                    $item[0].style.setProperty('display', 'flex', 'important');
                    hasResults = true;
                } else {
                    $item[0].style.setProperty('display', 'none', 'important');
                }
            });
            
            // Show message if no results
            if (hasResults) {
                $('#noTeacherCoursesResults').hide();
            } else {
                $('#noTeacherCoursesResults').show();
            }
        } else {
            $('#clearTeacherCoursesSearch').hide();
            $('#noTeacherCoursesResults').hide();
            $items.each(function() {
                $(this)[0].style.setProperty('display', 'flex', 'important');
            });
        }
    });
    
    $('#clearTeacherCoursesSearch').on('click', function() {
        $('#teacherCoursesSearchInput').val('');
        $('.teacher-course-item').each(function() {
            $(this)[0].style.setProperty('display', 'flex', 'important');
        });
        $('#noTeacherCoursesResults').hide();
        $(this).hide();
    });
    
    // Student: Enrolled Courses Search
    $('#enrolledCoursesSearchInput').on('keyup input paste', function() {
        var value = $(this).val().toLowerCase().trim();
        var hasResults = false;
        var $items = $('.enrolled-course-item');
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearEnrolledCoursesSearch').show();
            
            // Filter enrolled course items - only show matching items, explicitly hide others
            $items.each(function() {
                var $item = $(this);
                var itemText = $item.text().toLowerCase();
                
                if (itemText.indexOf(value) > -1) {
                    $item[0].style.setProperty('display', 'flex', 'important');
                    hasResults = true;
                } else {
                    $item[0].style.setProperty('display', 'none', 'important');
                }
            });
            
            // Show message if no results
            if (hasResults) {
                $('#noEnrolledCoursesResults').hide();
            } else {
                $('#noEnrolledCoursesResults').show();
            }
        } else {
            $('#clearEnrolledCoursesSearch').hide();
            $('#noEnrolledCoursesResults').hide();
            $items.each(function() {
                $(this)[0].style.setProperty('display', 'flex', 'important');
            });
        }
    });
    
    $('#clearEnrolledCoursesSearch').on('click', function() {
        $('#enrolledCoursesSearchInput').val('');
        $('.enrolled-course-item').each(function() {
            $(this)[0].style.setProperty('display', 'flex', 'important');
        });
        $('#noEnrolledCoursesResults').hide();
        $(this).hide();
    });
    
    // Student: Available Courses Search
    function filterAvailableCourses() {
        var value = $('#availableCoursesSearchInput').val().toLowerCase().trim();
        var hasResults = false;
        var $items = $('#available-courses .available-course-item');
        
        // Show/hide clear button
        if (value.length > 0) {
            $('#clearAvailableCoursesSearch').show();
            
            // Filter available course items - only show matching items, explicitly hide others
            $items.each(function() {
                var $item = $(this);
                var itemText = $item.text().toLowerCase();
                var element = this;
                
                // Get all text including button text
                var allText = $item.find('*').addBack().text().toLowerCase();
                
                if (allText.indexOf(value) > -1 || itemText.indexOf(value) > -1) {
                    // Show item - completely reset styles
                    element.style.cssText = '';
                    element.style.setProperty('display', 'flex', 'important');
                    element.style.setProperty('visibility', 'visible', 'important');
                    element.style.setProperty('opacity', '1', 'important');
                    element.style.setProperty('height', 'auto', 'important');
                    element.style.setProperty('margin', '', 'important');
                    element.style.setProperty('padding', '', 'important');
                    $item.removeClass('d-none').removeAttr('hidden');
                    hasResults = true;
                } else {
                    // Hide item - use cssText to override everything
                    element.style.cssText = 'display: none !important; visibility: hidden !important; opacity: 0 !important; height: 0 !important; overflow: hidden !important; margin: 0 !important; padding: 0 !important; border: none !important;';
                    $item.addClass('d-none').attr('hidden', 'hidden');
                }
            });
            
            // Show message if no results
            if (hasResults) {
                $('#noAvailableCoursesResults').hide();
            } else {
                $('#noAvailableCoursesResults').show();
            }
        } else {
            $('#clearAvailableCoursesSearch').hide();
            $('#noAvailableCoursesResults').hide();
            $items.each(function() {
                var element = this;
                element.style.cssText = '';
                element.style.setProperty('display', 'flex', 'important');
                $(this).removeClass('d-none').removeAttr('hidden');
            });
        }
    }
    
    $('#availableCoursesSearchInput').on('keyup input paste change', function() {
        filterAvailableCourses();
    });
    
    $('#clearAvailableCoursesSearch').on('click', function() {
        $('#availableCoursesSearchInput').val('');
        filterAvailableCourses();
        $(this).hide();
    });
    
    // Hide clear buttons initially
    $('#clearMaterialsSearch').hide();
    $('#clearStudentMaterialsSearch').hide();
    $('#clearTeacherMaterialsSearch').hide();
    $('#clearTeacherCoursesSearch').hide();
    $('#clearEnrolledCoursesSearch').hide();
    $('#clearAvailableCoursesSearch').hide();
    
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
                var titleLower = title.toLowerCase();
                var dateLower = currentDate.toLowerCase();
                enrolledUl.append(
                    '<li class="list-group-item d-flex justify-content-between align-items-center enrolled-course-item" '
                    + 'data-title="' + titleLower + '" '
                    + 'data-date="' + dateLower + '">'
                    + '<div class="d-flex align-items-center">'
                    + '<i class="bi bi-bookmark-check me-2 text-info"></i>'
                    + '<span>' + title + '</span>'
                    + '</div>'
                    + '<div class="d-flex align-items-center gap-2">'
                    + '<small class="text-muted">' + currentDate + '</small>'
                    + '<a href="/courses/manage/show/' + courseId + '" class="btn btn-sm btn-outline-success" title="View Course Details">'
                    + '<i class="bi bi-eye me-1"></i>View'
                    + '</a>'
                    + '</div>'
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
