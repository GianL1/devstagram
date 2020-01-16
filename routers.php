<?php
global $routes;
$routes = array();

$routes['/users/login'] = '/users/login';
$routes['/users/new'] = '/users/new_record';

$routes['/users/feed'] = '/users/feed'; //MEU FEED

$routes['/users/{id}'] = '/users/view/:id'; //GET, PUT, DELETE

$routes['/users/{id}/feed'] = '/users/feed/:id';
$routes['/users/{id}/fotos'] = '/users/fotos/:id';
$routes['/users/{id}/follow'] = '/users/follow/:id';

$routes['/fotos/random'] = '/fotos/random';
$routes['/fotos/new'] = '/fotos/new_foto';
$routes['/fotos/{id}'] = '/fotos/view/:id';
$routes['/fotos/{id}/comment'] = '/fotos/comment/:id';
$routes['/fotos/{id}/like'] = '/fotos/like/:id';

$routes['/comments/{id}'] = '/fotos/delete_comment/:id';