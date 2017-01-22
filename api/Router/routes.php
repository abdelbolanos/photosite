<?php
use Controller as Controller;

global $routes;
$routes = [
    [
        'route' => '/categories/resized/(.*)',
        'controller' => Controller\Controller::class,
        'action' => 'getResizedImage',
        'type' => 'image'
    ],
    [
        'route' => '/resize-image/([0-9]+)/width/([0-9]+)/height/([0-9]+)',
        'controller' => Controller\Controller::class,
        'action' => 'resizeImage',
        'type' => 'resize'
    ],
    [
        'route' => 'admin/list',
        'controller' => Controller\AdminController::class,
        'action' => 'list',
        'type' => 'controller'
    ],
    [
        'route' => 'admin/photo/(.*)',
        'controller' => Controller\AdminController::class,
        'action' => 'photo',
        'type' => 'controller'
    ],
    [
        'route' => 'admin/add',
        'controller' => Controller\AdminController::class,
        'action' => 'add',
        'type' => 'controller'
    ],
    [
        'route' => 'admin/delete/(.*)',
        'controller' => Controller\AdminController::class,
        'action' => 'delete',
        'type' => 'controller'
    ],
    [
        'route' => 'admin/update',
        'controller' => Controller\AdminController::class,
        'action' => 'update',
        'type' => 'controller'
    ],
    [
        'route' => 'photo/list',
        'controller' => Controller\PhotoController::class,
        'action' => 'list',
        'type' => 'controller'
    ],
    [
        'route' => 'photo/newest',
        'controller' => Controller\PhotoController::class,
        'action' => 'newest',
        'type' => 'controller'
    ],
    [
        'route' => 'category/(.*)',
        'controller' => Controller\PhotoController::class,
        'action' => 'category',
        'type' => 'controller'
    ],
    [
        'route' => 'allCategories',
        'controller' => Controller\PhotoController::class,
        'action' => 'allCategories',
        'type' => 'controller'
    ],
];
