<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ITE311 Project') ?></title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        #notif-count {
            font-size: 0.75rem;
            padding: 3px 6px;
            border-radius: 50%;
            position: relative;
            top: -8px;
            right: 5px;
        }
        .dropdown-menu {
            max-height: 400px;
            overflow-y: auto;
            width: 350px;
            padding: 0.5rem;
        }
        .notification-item {
            font-size: 0.9rem;
            word-wrap: break-word;
            margin-bottom: 0.5rem;
        }
        .notification-item:last-child {
            margin-bottom: 0;
        }
        .notification-alert {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard">ITE311 System</a>
      
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">

                <!-- Common links -->
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard">Dashboard</a>
                </li>

                <?php if (in_array(session()->get('role'), ['admin', 'teacher'], true)): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('courses/manage') ?>">Courses</a>
                    </li>
                <?php endif; ?>

                <?php if (session()->get('role') === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/users') ?>">Manage Users</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">

                <?php if (session()->get('isLoggedIn')): ?>
                    
                    <!-- 🔔 Notification Bell -->
                <li class="nav-item dropdown me-3" id="notifDropdown">
                     <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown">
                         <i class="bi bi-bell-fill"></i>
                         <span id="notif-count" class="badge bg-danger" style="display: none;">0</span>
                     </a>
                    
                        <ul class="dropdown-menu dropdown-menu-end" id="notif-list">
                            <li class="dropdown-item-text">
                                <p class="text-center text-muted m-2 mb-0">Loading notifications...</p>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <span class="navbar-text text-light me-2">
                            <?= esc(session()->get('email')) ?> (<?= esc(session()->get('role')) ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger btn-sm text-white" href="/logout">Logout</a>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

<!-- 🧠 Notifications Script -->
<script>
$(document).ready(function() {
    window.csrfTokenName = window.csrfTokenName || '<?= csrf_token() ?>';
    window.csrfTokenValue = window.csrfTokenValue || '<?= csrf_hash() ?>';

    function loadNotifications() {
        $.get("<?= base_url('notifications'); ?>", function(data) {
            if (!data || typeof data !== "object") {
                console.error("Invalid data from server:", data);
                return;
            }

            // Update count badge
            if (data.count > 0) {
                $("#notif-count").text(data.count).show();
            } else {
                $("#notif-count").hide();
            }

            // Build list with Bootstrap alert classes
            let html = '';
            if (data.notifications.length === 0) {
                html = '<li class="dropdown-item-text"><p class="text-center text-muted m-2 mb-0">No new notifications</p></li>';
            } else {
                data.notifications.forEach(n => {
                    // Use alert-info for unread, alert-secondary for read
                    const alertClass = n.is_read == 1 ? 'alert-secondary' : 'alert-info';
                    const readClass = n.is_read == 1 ? 'text-muted' : 'fw-bold';
                    html += `
                        <li class="dropdown-item-text p-0">
                            <div class="alert ${alertClass} notification-alert d-flex justify-content-between align-items-start mb-0 ${readClass}" role="alert">
                                <div class="notification-item text-wrap flex-grow-1 me-2">${n.message}</div>
                                ${n.is_read == 0 ? `<button class="btn btn-sm btn-outline-success mark-read flex-shrink-0" data-id="${n.id}" title="Mark as read">✓</button>` : ''}
                            </div>
                        </li>
                    `;
                });
            }

            $("#notif-list").html(html);
        }).fail(function(xhr) {
            console.error("Notification fetch failed:", xhr.responseText);
            $("#notif-list").html('<li class="dropdown-item-text"><div class="alert alert-danger notification-alert mb-0" role="alert"><p class="text-center mb-0">Failed to load notifications</p></div></li>');
        });
    }

    // Mark notification as read
    $(document).on("click", ".mark-read", function(e) {
        e.preventDefault();
        const id = $(this).data("id");
        const $button = $(this);
        
        // Disable button to prevent double-clicks
        $button.prop('disabled', true);
        
        var postData = {};
        postData[window.csrfTokenName] = window.csrfTokenValue;

        $.post("<?= base_url('notifications/mark_read/'); ?>" + id, postData, function(res) {
            if (res && res.csrf_token && res.csrf_hash) {
                window.csrfTokenName = res.csrf_token;
                window.csrfTokenValue = res.csrf_hash;
            }

            if (res.success) {
                // Reload notifications to update the list and badge count
                loadNotifications();
            } else {
                // Re-enable button if failed
                $button.prop('disabled', false);
            }
        }).fail(function(xhr) {
            console.error("Mark as read failed:", xhr.responseText);
            // Re-enable button on error
            $button.prop('disabled', false);
        });
    });

    // Initial load on page ready so badge shows correct count after reload
    loadNotifications();

    // Allow other scripts to trigger a notification refresh
    $(document).on('refreshNotifications', function () {
        loadNotifications();
    });

    // Load notifications when dropdown is shown (on click)
    $('#notifDropdown').on('show.bs.dropdown', function () {
        loadNotifications();
    });

  
    setInterval(function() {
        $.get("<?= base_url('notifications'); ?>", function(data) {
            if (data && typeof data === "object" && data.count > 0) {
                $("#notif-count").text(data.count).show();
            } else if (data && data.count === 0) {
                $("#notif-count").hide();
            }
        });
    }, 60000);
});
</script>

