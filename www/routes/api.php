<?php

use App\Core\Router;

$router = new Router();

// Exemplo de Middleware por path
$router->addMiddleware('/*', 'ExampleMiddleware@logs');

$router->get('/', 'ExampleController@index');

$router->get('/test', 'ExampleController@testGet');
$router->post('/test', 'ExampleController@testPost', ['ExampleMiddleware@handle'], ['some_data' => 'example']);
$router->put('/test', 'ExampleController@testPut');
$router->delete('/test', 'ExampleController@testDelete');

// Example Users 

$router->get('/users', 'ExampleUserController@index');
$router->get('/users/{user_id}', 'ExampleUserController@show');
$router->post('/users', 'ExampleUserController@create');
$router->put('/users/{user_id}', 'ExampleUserController@update');
$router->delete('/users/{user_id}', 'ExampleUserController@delete', ['ExampleAuthMiddleware@handle'], ['permissions' => 'users.delete']);

// Example Auth

$router->post('/auth/login', 'ExampleAuthController@login');
$router->get('/auth/authenticated', 'ExampleAuthController@authenticated', ['ExampleAuthMiddleware@handle']);

$router->dispatch(); 