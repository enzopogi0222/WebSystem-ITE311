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
        <!-- Search Bar for Users -->
        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="userSearchInput" class="form-control" placeholder="Search users by ID, name, email, role, or status...">
                <button class="btn btn-outline-secondary" type="button" id="clearUserSearch">
                    <i class="bi bi-x"></i> Clear
                </button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-success">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-row" 
                            data-id="<?= esc($user['id']) ?>"
                            data-name="<?= strtolower(esc($user['name'] ?? '')) ?>"
                            data-email="<?= strtolower(esc($user['email'] ?? '')) ?>"
                            data-role="<?= strtolower(esc($user['role'] ?? '')) ?>"
                            data-status="<?= strtolower(esc($user['status'] ?? 'active')) ?>">
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
                            <td>
                                <?php
                                    // Normalize status string for display
                                    $status = strtolower(trim($user['status'] ?? 'active'));

                                    switch ($status) {
                                        case 'active':
                                            $statusBadgeClass = 'bg-success';
                                            $statusLabel = 'Active';
                                            break;
                                        case 'inactive':
                                            $statusBadgeClass = 'bg-danger';
                                            $statusLabel = 'Inactive';
                                            break;
                                        default:
                                            $statusBadgeClass = 'bg-secondary';
                                            $statusLabel = 'Unknown';
                                            break;
                                    }
                                ?>
                                <span class="badge <?= $statusBadgeClass; ?>">
                                    <?= esc($statusLabel) ?>
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
                        <td colspan="6" class="text-center text-muted">No users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            <div id="noUsersResults" class="text-center text-muted py-3" style="display: none;">
                <i class="bi bi-search"></i> No users found matching your search.
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // User Search Functionality
    $('#userSearchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        var hasResults = false;
        
        $('.user-row').each(function() {
            var id = $(this).data('id').toString();
            var name = $(this).data('name') || '';
            var email = $(this).data('email') || '';
            var role = $(this).data('role') || '';
            var status = $(this).data('status') || '';
            
            // Get role label for search
            var roleLabel = '';
            switch(role) {
                case 'admin':
                    roleLabel = 'admin';
                    break;
                case 'teacher':
                    roleLabel = 'teacher';
                    break;
                case 'student':
                    roleLabel = 'student';
                    break;
            }
            
            // Get status label for search
            var statusLabel = '';
            switch(status) {
                case 'active':
                    statusLabel = 'active';
                    break;
                case 'inactive':
                    statusLabel = 'inactive';
                    break;
            }
            
            var searchText = id + ' ' + name + ' ' + email + ' ' + roleLabel + ' ' + statusLabel;
            
            if (searchText.indexOf(value) > -1) {
                $(this).show();
                hasResults = true;
            } else {
                $(this).hide();
            }
        });
        
        if (value.length > 0) {
            $('#clearUserSearch').show();
            if (hasResults) {
                $('#noUsersResults').hide();
            } else {
                $('#noUsersResults').show();
            }
        } else {
            $('#clearUserSearch').hide();
            $('#noUsersResults').hide();
        }
    });
    
    $('#clearUserSearch').on('click', function() {
        $('#userSearchInput').val('');
        $('.user-row').show();
        $('#noUsersResults').hide();
        $(this).hide();
    });
    
    // Hide clear button initially
    $('#clearUserSearch').hide();
});
</script>

</body>
</html>