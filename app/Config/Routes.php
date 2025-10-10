<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Routes
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::index'); 
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Authentication Routes
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

$routes->get('dashboard', 'Auth::dashboard');
$routes->get('/admin/dashboard', 'Admin::dashboard');
$routes->get('/teacher/dashboard', 'Teacher::dashboard');
$routes->get('/student/dashboard', 'Students::dashboard');
$routes->post('/course/enroll', 'Course::enroll');
$routes->setAutoRoute(true);
