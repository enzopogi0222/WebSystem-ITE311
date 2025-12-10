<?= $this->include('template/header'); ?>

<div class="row mb-3">
    <div class="col-md-8 offset-md-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Edit User</h1>
            <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary btn-sm">
                Back to Users
            </a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="post" action="<?= base_url('admin/users/update/' . $user['id']) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control <?= isset($validation) && $validation->hasError('name') ? 'is-invalid' : '' ?>"
                               value="<?= esc($user['name']) ?>">
                        <?php if (isset($validation) && $validation->hasError('name')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('name') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>"
                               pattern="[a-zA-Z0-9]+"
                               title="Password must contain only letters and numbers. No special characters allowed.">
                        <?php if (isset($validation) && $validation->hasError('password')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('password') ?>
                            </div>
                        <?php endif; ?>
                        <div class="invalid-feedback" id="password-error" style="display: none;">
                            Password must contain only letters and numbers. No special characters allowed.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm New Password</label>
                        <input type="password"
                               id="password_confirm"
                               name="password_confirm"
                               class="form-control <?= isset($validation) && $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>"
                               pattern="[a-zA-Z0-9]+"
                               title="Password must contain only letters and numbers. No special characters allowed.">
                        <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('password_confirm') ?>
                            </div>
                        <?php endif; ?>
                        <div class="invalid-feedback" id="password_confirm-error" style="display: none;">
                            Password must contain only letters and numbers. No special characters allowed.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>"
                               value="<?= esc($user['email']) ?>"
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                               title="Email must contain @ and no special characters except @, ., _, %, +, -"
                               required>
                        <?php if (isset($validation) && $validation->hasError('email')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('email') ?>
                            </div>
                        <?php endif; ?>
                        <div class="invalid-feedback" id="email-error" style="display: none;">
                            Email must contain @ and no special characters except @, ., _, %, +, -
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select <?= isset($validation) && $validation->hasError('role') ? 'is-invalid' : '' ?>">
                            <?php $currentRole = $user['role']; ?>
                            <option value="admin"   <?= $currentRole === 'admin'   ? 'selected' : '' ?>>Admin</option>
                            <option value="teacher" <?= $currentRole === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                            <option value="student" <?= $currentRole === 'student' ? 'selected' : '' ?>>Student</option>
                        </select>
                        <?php if (isset($validation) && $validation->hasError('role')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('role') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select <?= isset($validation) && $validation->hasError('status') ? 'is-invalid' : '' ?>">
                            <?php $currentStatus = $user['status'] ?? 'active'; ?>
                            <option value="active"   <?= $currentStatus === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <?php if (isset($validation) && $validation->hasError('status')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('status') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Frontend validation for email and password
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirm');
        const form = document.querySelector('form');

        // Email validation
        emailInput.addEventListener('input', function() {
            const email = this.value;
            const emailError = document.getElementById('email-error');
            
            if (email && !email.includes('@')) {
                this.setCustomValidity('Email must contain @ symbol');
                emailError.style.display = 'block';
                this.classList.add('is-invalid');
            } else if (email && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                this.setCustomValidity('Invalid email format. No special characters except @, ., _, %, +, -');
                emailError.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                emailError.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        // Password validation (alphanumeric only) - only if password is provided
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const passwordError = document.getElementById('password-error');
            
            // Only validate if password is not empty (since it's optional)
            if (password && !/^[a-zA-Z0-9]+$/.test(password)) {
                this.setCustomValidity('Password must contain only letters and numbers. No special characters allowed.');
                passwordError.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                passwordError.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        // Confirm Password validation (alphanumeric only) - only if password is provided
        passwordConfirmInput.addEventListener('input', function() {
            const passwordConfirm = this.value;
            const passwordConfirmError = document.getElementById('password_confirm-error');
            
            // Only validate if password confirm is not empty (since it's optional)
            if (passwordConfirm && !/^[a-zA-Z0-9]+$/.test(passwordConfirm)) {
                this.setCustomValidity('Password must contain only letters and numbers. No special characters allowed.');
                passwordConfirmError.style.display = 'block';
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                passwordConfirmError.style.display = 'none';
                this.classList.remove('is-invalid');
            }
        });

        // Form submission validation
        form.addEventListener('submit', function(e) {
            const email = emailInput.value;
            const password = passwordInput.value;
            const passwordConfirm = passwordConfirmInput.value;

            // Validate email
            if (!email.includes('@') || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                e.preventDefault();
                emailInput.focus();
                return false;
            }

            // Validate password only if provided
            if (password && !/^[a-zA-Z0-9]+$/.test(password)) {
                e.preventDefault();
                passwordInput.focus();
                return false;
            }

            // Validate confirm password only if provided
            if (passwordConfirm && !/^[a-zA-Z0-9]+$/.test(passwordConfirm)) {
                e.preventDefault();
                passwordConfirmInput.focus();
                return false;
            }
        });
    });
</script>
