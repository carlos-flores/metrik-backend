<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class PruebaController extends Controller
{
    public function prueba(Request $request)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            return "usuario autenticado";
        } else {
            return "usuario NO autenticado";
        }
    }

}
