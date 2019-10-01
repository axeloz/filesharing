<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    function getUser(Request $request)
    {
        return $request->user();
    }
}