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
                               class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>">
                        <?php if (isset($validation) && $validation->hasError('password')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('password') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirm New Password</label>
                        <input type="password"
                               id="password_confirm"
                               name="password_confirm"
                               class="form-control <?= isset($validation) && $validation->hasError('password_confirm') ? 'is-invalid' : '' ?>">
                        <?php if (isset($validation) && $validation->hasError('password_confirm')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('password_confirm') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>"
                               value="<?= esc($user['email']) ?>">
                        <?php if (isset($validation) && $validation->hasError('email')): ?>
                            <div class="invalid-feedback">
                                <?= $validation->getError('email') ?>
                            </div>
                        <?php endif; ?>
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
