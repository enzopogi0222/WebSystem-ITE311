<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="/">ITE311-GAGNI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('home') ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="<?= base_url('courses/search') ?>">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('about') ?>">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('contact') ?>">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                       <a class="nav-link" href="<?= base_url('login') ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('register') ?>">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container mt-5">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h1 class="h3 mb-0">Courses</h1>
                </div>
                <div class="card-body">

    <div class="row mb-4">
        <div class="col-md-6">
            <form id="searchForm" class="d-flex" method="get" action="<?= base_url('/courses/search') ?>">
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search courses..." name="search_term" value="<?= esc($searchTerm ?? '') ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="coursesContainer" class="row mt-3">
        <?php if (!empty($courses)): ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4 mb-4">
                    <div class="card course-card">
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($course['title']) ?></h5>
                            <p class="card-text"><?= esc($course['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No courses found.</div>
            </div>
        <?php endif; ?>
    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    // Client-side filtering
    $('#searchInput').on('keyup', function () {
        var value = $(this).val().toLowerCase();
        $('.course-card').filter(function () {
            return $(this).text().toLowerCase().indexOf(value) > -1;
        }).show();

        $('.course-card').filter(function () {
            return $(this).text().toLowerCase().indexOf(value) === -1;
        }).hide();
    });

    // Server-side search with AJAX
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();

        var searchTerm = $('#searchInput').val();

        $.get('<?= base_url('/courses/search') ?>', { search_term: searchTerm }, function (data) {
            var $container = $('#coursesContainer');
            $container.empty();

            if (data && data.length) {
                $.each(data, function (index, course) {
                    var cardHtml =
                        '<div class="col-md-4 mb-4 course-card">' +
                            '<div class="card h-100">' +
                                '<div class="card-body">' +
                                    '<h5 class="card-title">' + (course.title ?? '') + '</h5>' +
                                    '<p class="card-text">' + (course.description ?? '') + '</p>' +
                                '</div>' +
                            '</div>' +
                        '</div>';

                    $container.append(cardHtml);
                });
            } else {
                $container.html('<div class="col-12"><div class="alert alert-info">No courses found matching your search.</div></div>');
            }
        }, 'json');
    });
});
</script>
</body>
</html>
