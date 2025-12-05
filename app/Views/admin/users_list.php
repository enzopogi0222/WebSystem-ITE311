<?= $this->include('template/header'); ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">User Management</h1>
    <a href="<?= site_url('admin/users/create') ?>" class="btn btn-success">
        <i class="bi bi-person-plus-fill"></i> Create New User
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-success">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= esc($user['id']) ?></td>
                            <td><?= esc($user['name']) ?></td>
                            <td><?= esc($user['email']) ?></td>
                            <td>
                                <?php
                                    // Normalize role string for display
                                    $role = strtolower(trim($user['role'] ?? ''));

                                    switch ($role) {
                                        case 'admin':
                                            $badgeClass = 'bg-primary';
                                            $label = 'Admin';
                                            break;
                                        case 'teacher':
                                            $badgeClass = 'bg-warning text-dark';
                                            $label = 'Teacher';
                                            break;
                                        case 'student':
                                            $badgeClass = 'bg-info text-dark';
                                            $label = 'Student';
                                            break;
                                        default:
                                            $badgeClass = 'bg-secondary';
                                            $label = 'Unknown';
                                            break;
                                    }
                                ?>
                                <span class="badge <?= $badgeClass; ?>">
                                    <?= esc($label) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('admin/users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="<?= site_url('admin/users/delete/' . $user['id']) ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this user?');">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>