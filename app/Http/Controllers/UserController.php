<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\JwtAuth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        Log::info('registrando...');
        // se debe de recoger lo recibdo por post
        $json = $request->input('json', null);
        Log::info($json);
        if (!is_null($json)) {
            $params = json_decode($json);

            if (isset($params->email) && isset($params->name) && isset($params->surname) && isset($params->password)) {
                $nuevoUsuario = new User;
                $nuevoUsuario->name = $params->name;
                $nuevoUsuario->surname = $params->surname;
                $nuevoUsuario->email = $params->email;
                $nuevoUsuario->role = 'user';
                $nuevoUsuario->password = hash('sha256', $params->password);
                $isset_user = User::where('email', '=', $params->email)->first();
                $count = is_array($isset_user) ? count($isset_user) : 0;
                if ($isset_user) {
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Ya existe email registrado en sistema',
                    );
                } else {
                    $nuevoUsuario->save();
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario registrado correctamente en el sistema',
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se han recibido todos los datos del usuario',
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se han recibido datos del usuario',
            );
        }
        return response()->json($data, 200);
    }

    public function login(Request $request){
        $jwtAuth = new JwtAuth();
    
        // se debe de recoger lo recibido por post
        $json = $request->input('json',null);
    
        if (!is_null($json)) {
          $params = json_decode($json);
          if (isset($params->email) && isset($params->password) && isset($params->getToken)) {
            $email = $params->email;
            $password = $params->password;
            $getToken = $params->getToken;
            $pwdCifrado = hash('sha256',$password);
            $signup = $jwtAuth->signup($email,$pwdCifrado ,$getToken);
            return response()->json($signup,200);
          }else{
            $data = array(
              'status'=>'error',
              'code'=>400,
              'message'=>'No se puede hacer login, faltan datos'
            );
            return response()->json($data,200);
          }
        }else{
          $data = array(
            'status'=>'error',
            'code'=>400,
            'message'=>'No se puede hacer login, faltan datos'
          );
          return response()->json($data,200);
        }
      }

    public function loginBCK(Request $request){
        $jwtAuth = new JwtAuth();
    
        // se debe de recoger lo recibido por post
        $json = $request->input('json',null);
    
        if (!is_null($json)) {
          $params = json_decode($json);
          if (isset($params->email) && isset($params->password) && isset($params->getToken)) {
            $email = $params->email;
            $password = $params->password;
            $getToken = $params->getToken;
            $pwdCifrado = hash('sha256',$password);
            $signup = $jwtAuth->signup($email,$pwdCifrado ,$getToken);
            return response()->json($signup,200);
          }else{
            $data = array(
              'status'=>'error',
              'code'=>400,
              'message'=>'No se puede hacer login, faltan datos'
            );
            return response()->json($data,200);
          }
        }else{
          $data = array(
            'status'=>'error',
            'code'=>400,
            'message'=>'No se puede hacer login, faltan datos'
          );
          return response()->json($data,200);
        }
      }

  }
