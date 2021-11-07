<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    function baseURL()
    {
        return env('APP_URL') . 'api/v1/';
    }

    function baseImage()
    {
        return env('APP_URL') . 'post/image/';
    }
}
