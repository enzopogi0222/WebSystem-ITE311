<?= $this->include('template/header', ['title' => 'Course Materials']) ?>

<div class="container mt-4">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('/dashboard') ?>" class="text-decoration-none text-success">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('/dashboard') ?>" class="text-decoration-none text-success">
                        <i class="fas fa-graduation-cap me-1"></i>Courses
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-folder-open me-1"></i>Course <?= esc($course_id) ?> Materials
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white">
                            <i class="fas fa-folder-open me-2"></i>Materials Management
                            <?php if (!empty($materials)): ?>
                                <span class="badge bg-light text-success ms-2"><?= count($materials) ?></span>
                            <?php endif; ?>
                        </h4>
                        <?php if (in_array($user['role'], ['admin', 'teacher'])): ?>
                            <a href="<?= base_url('/materials/upload/' . $course_id) ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-upload me-2"></i>Upload Material
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Flash Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        
                        <!-- Search Bar for Materials -->
                        <?php if (!empty($materials)): ?>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="materialsListSearchInput" class="form-control" placeholder="Search materials by file name, type, or date...">
                                <button class="btn btn-outline-secondary" type="button" id="clearMaterialsListSearch">
                                    <i class="bi bi-x"></i> Clear
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($materials)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th><i class="fas fa-file me-2"></i>File Name</th>
                                            <th><i class="fas fa-info-circle me-2"></i>File Type</th>
                                            <th><i class="fas fa-calendar me-2"></i>Upload Date</th>
                                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="materialsTableBody">
                                        <?php foreach ($materials as $material): ?>
                                            <?php
                                            $fileExtension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                            $uploadDate = date('M j, Y g:i A', strtotime($material['created_at']));
                                            $uploadDateShort = date('M j, Y', strtotime($material['created_at']));
                                            ?>
                                            <tr class="material-row" 
                                                data-filename="<?= strtolower(esc($material['file_name'] ?? '')) ?>"
                                                data-filetype="<?= strtolower($fileExtension) ?>"
                                                data-uploaddate="<?= strtolower($uploadDate) ?>"
                                                data-uploaddateshort="<?= strtolower($uploadDateShort) ?>">
                                                <td>
                                                    <?php
                                                    $fileExtension = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                                    $fileIcon = 'fas fa-file';
                                                    $iconColor = 'text-secondary';
                                                    
                                                    switch ($fileExtension) {
                                                        case 'pdf':
                                                            $fileIcon = 'fas fa-file-pdf';
                                                            $iconColor = 'text-danger';
                                                            break;
                                                        case 'doc':
                                                        case 'docx':
                                                            $fileIcon = 'fas fa-file-word';
                                                            $iconColor = 'text-primary';
                                                            break;
                                                        case 'ppt':
                                                        case 'pptx':
                                                            $fileIcon = 'fas fa-file-powerpoint';
                                                            $iconColor = 'text-warning';
                                                            break;
                                                        case 'jpg':
                                                        case 'jpeg':
                                                        case 'png':
                                                            $fileIcon = 'fas fa-file-image';
                                                            $iconColor = 'text-success';
                                                            break;
                                                        case 'txt':
                                                            $fileIcon = 'fas fa-file-alt';
                                                            $iconColor = 'text-info';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?= $fileIcon ?> <?= $iconColor ?> me-2"></i>
                                                    <?= esc($material['file_name']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?= strtoupper($fileExtension) ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= $uploadDate ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="<?= base_url('/materials/download/' . $material['id']) ?>" 
                                                           class="btn btn-outline-success" 
                                                           title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <?php if (in_array($user['role'], ['admin', 'teacher'])): ?>
                                                            <a href="<?= base_url('/materials/delete/' . $material['id']) ?>" 
                                                               class="btn btn-outline-danger" 
                                                               title="Delete"
                                                               onclick="return confirm('Are you sure you want to delete this material?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="noMaterialsListResults" class="text-center text-muted py-3" style="display: none;">
                                <i class="bi bi-search"></i> No materials found matching your search.
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Materials Available</h5>
                                <p class="text-muted">No course materials have been uploaded yet.</p>
                                <?php if (in_array($user['role'], ['admin', 'teacher'])): ?>
                                    <a href="<?= base_url('/materials/upload/' . $course_id) ?>" class="btn btn-success">
                                        <i class="fas fa-upload me-2"></i>Upload First Material
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-3">
                    <a href="<?= base_url('/dashboard') ?>" class="btn btn-success">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Materials List Search Functionality
    $('#materialsListSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        var hasResults = false;
        
        $('.material-row').each(function() {
            var filename = $(this).data('filename') || '';
            var filetype = $(this).data('filetype') || '';
            var uploaddate = $(this).data('uploaddate') || '';
            var uploaddateshort = $(this).data('uploaddateshort') || '';
            
            var searchText = filename + ' ' + filetype + ' ' + uploaddate + ' ' + uploaddateshort;
            
            if (searchText.indexOf(value) > -1) {
                $(this).show();
                hasResults = true;
            } else {
                $(this).hide();
            }
        });
        
        if (value.length > 0) {
            $('#clearMaterialsListSearch').show();
            if (hasResults) {
                $('#noMaterialsListResults').hide();
            } else {
                $('#noMaterialsListResults').show();
            }
        } else {
            $('#clearMaterialsListSearch').hide();
            $('#noMaterialsListResults').hide();
        }
    });
    
    $('#clearMaterialsListSearch').on('click', function() {
        $('#materialsListSearchInput').val('');
        $('.material-row').show();
        $('#noMaterialsListResults').hide();
        $(this).hide();
    });
    
    // Hide clear button initially
    $('#clearMaterialsListSearch').hide();
});
</script>

</body>
</html>
