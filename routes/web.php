<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () {
    return response("");
});

$router->group(['prefix' => '/api/v1/'], function () use ($router) {
    /* Authentication Services */
    $router->post('authentication/register/', 'AuthController@register');
    $router->post('authentication/login/', 'AuthController@login');
    /* End Authentication Services */
    
    /* User Services */
    $router->group(['prefix' => 'user', 'middleware' => 'auth'], function () use ($router) {
        $router->get('/', 'UserController@show');
        $router->post('/update/', 'UserController@update');
        $router->get('/logout/', 'UserController@logout');
    });
    /* End User Services */
});
