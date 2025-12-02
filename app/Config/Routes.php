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

$routes->get('/dashboard', 'Auth::dashboard');

// Admin Routes
$routes->get('admin/users', 'Admin::users');                       
$routes->get('admin/users/create', 'Admin::createUser');          
$routes->post('admin/users/store', 'Admin::storeUser');           
$routes->get('admin/users/edit/(:num)', 'Admin::editUser/$1');     
$routes->post('admin/users/update/(:num)', 'Admin::updateUser/$1');
$routes->get('admin/users/delete/(:num)', 'Admin::deleteUser/$1'); 
$routes->post('admin/users/role/(:num)', 'Admin::changeRole/$1');  

$routes->post('/course/enroll', 'Course::enroll');
$routes->get('/courses/search', 'Course::search');
$routes->post('/courses/search', 'Course::search');

$routes->get('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->post('/admin/course/(:num)/upload', 'Materials::upload/$1');
$routes->get('/materials/course/(:num)', 'Materials::course/$1');
$routes->get('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->post('/materials/upload/(:num)', 'Materials::upload/$1');
$routes->get('/materials/delete/(:num)', 'Materials::delete/$1');
$routes->get('/materials/download/(:num)', 'Materials::download/$1');

$routes->get('/notifications', 'Notifications::get');
$routes->post('/notifications/mark_read/(:num)', 'Notifications::mark_as_read/$1');

$routes->setAutoRoute(true);