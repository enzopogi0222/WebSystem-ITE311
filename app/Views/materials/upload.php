<?= $this->include('template/header', ['title' => 'Upload Material']) ?>

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
                    <a href="<?= base_url('/materials/course/' . $course_id) ?>" class="text-decoration-none text-success">
                        <i class="fas fa-folder-open me-1"></i>Course <?= esc($course_id) ?> Materials
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-upload me-1"></i>Upload Material
                </li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-upload me-2"></i>Upload Course Material
                        </h4>
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

                        <!-- Upload Form -->
                        <form action="<?= base_url('/materials/upload/' . $course_id) ?>" method="post" enctype="multipart/form-data" id="uploadForm">
                            <?= csrf_field() ?>
                            
                            <div class="mb-4">
                                <label for="material_file" class="form-label fw-bold">
                                    <i class="fas fa-file me-2"></i>Select Material File
                                </label>
                                <input type="file" 
                                       class="form-control form-control-lg" 
                                       id="material_file" 
                                       name="material_file" 
                                       accept=".pdf,.ppt,.pptx"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Allowed file types: PDF, PPT, PPTX. Maximum size: 10MB
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle text-info me-2"></i>Upload Guidelines
                                        </h6>
                                        <ul class="mb-0 small">
                                            <li>Ensure your file is properly named and organized</li>
                                            <li>Check that the content is appropriate for the course</li>
                                            <li>Large files may take longer to upload</li>
                                            <li>Students will be able to download this material once uploaded</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="<?= base_url('/materials/course/' . $course_id) ?>" 
                                   class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Materials
                                </a>
                                <button type="submit" class="btn btn-success" id="uploadBtn">
                                    <i class="fas fa-upload me-2"></i>Upload Material
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- File Preview Section -->
                <div class="card shadow mt-4" id="previewCard" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-eye me-2"></i>File Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="fileInfo"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('material_file');
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadForm = document.getElementById('uploadForm');
            const previewCard = document.getElementById('previewCard');
            const fileInfo = document.getElementById('fileInfo');

            // File input change event
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Show file preview
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                    const maxSize = 10;

                    let fileIcon = 'fas fa-file';
                    if (file.type.includes('pdf')) fileIcon = 'fas fa-file-pdf text-danger';
                    else if (file.type.includes('word') || file.name.includes('.doc')) fileIcon = 'fas fa-file-word text-primary';
                    else if (file.type.includes('powerpoint') || file.name.includes('.ppt')) fileIcon = 'fas fa-file-powerpoint text-warning';
                    else if (file.type.includes('image')) fileIcon = 'fas fa-file-image text-success';
                    else if (file.type.includes('text')) fileIcon = 'fas fa-file-alt text-info';

                    fileInfo.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="${fileIcon} fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">${file.name}</h6>
                                <small class="text-muted">Size: ${fileSize} MB</small>
                                ${fileSize > maxSize ? '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> File size exceeds 10MB limit</small>' : ''}
                            </div>
                        </div>
                    `;

                    previewCard.style.display = 'block';

                    // Disable upload button if file is too large
                    if (fileSize > maxSize) {
                        uploadBtn.disabled = true;
                        uploadBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>File Too Large';
                    } else {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Material';
                        uploadBtn.className = 'btn btn-success';
                    }
                } else {
                    previewCard.style.display = 'none';
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Material';
                    uploadBtn.className = 'btn btn-success';
                }
            });

            // Form submission
            uploadForm.addEventListener('submit', function() {
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
            });
        });
    </script>
</body>
</html>
