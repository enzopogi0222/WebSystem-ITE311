<?php

declare(strict_types=1);

use CodeIgniter\Boot;
use Config\Paths;

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure current directory is correct
chdir(FCPATH);

// Load our paths config file
require __DIR__ . '/app/Config/Paths.php';

$paths = new Paths();

// Load the framework bootstrap and run the app
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

exit(Boot::bootWeb($paths));
