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
        /* User Profile Services */
        $router->get('/', 'UserController@show');
        $router->post('/update/', 'UserController@update');
        $router->get('/logout/', 'UserController@logout');
        /* User Profile Services */
        
        /* User Post Services */
        $router->get('/post/', 'PostController@index');
        $router->post('/post/', 'PostController@store');
        $router->get('/post/{postId}/', 'PostController@show');
        $router->post('/post/{postId}/', 'PostController@update');
        $router->delete('/post/{postId}/', 'PostController@delete');
        /* End User Post Services */
    });
    /* End User Services */

    /* Post Services */
    $router->group(['prefix' => 'post'], function () use ($router) {
        $router->get('/', 'PostController@list');
        $router->get('/{postId}/', 'PostController@detail');
    });
    /* End Post Services */
});
