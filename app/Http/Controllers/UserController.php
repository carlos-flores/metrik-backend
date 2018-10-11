<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\ResUsersMapa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            Log::info('registrando...');
            // se debe de recoger lo recibdo por post
            $json = $request->input('json', null);
            Log::info($json);
            if (!is_null($json)) {
                $params = json_decode($json);

                if (isset($params->email) && isset($params->name) && isset($params->surname) && isset($params->password) && isset($params->role)) {
                    $nuevoUsuario = new ResUsersMapa;
                    $nuevoUsuario->name = $params->name;
                    $nuevoUsuario->surname = $params->surname;
                    $nuevoUsuario->email = $params->email;
                    $nuevoUsuario->role = $params->role;
                    $nuevoUsuario->password = hash('sha256', $params->password);
                    $isset_user = new ResUsersMapa;
                    $isset_user = ResUsersMapa::where('email', '=', $params->email)->first();
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

        } else {
            return "usuario NO autenticado";
        }

    }

    public function update(Request $request, $userId)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            Log::info('actualizando...');
            // se debe de recoger lo recibdo por post
            $json = $request->input('json', null);
            Log::info($json);
            if (!is_null($json)) {
                $params = json_decode($json);

                if (isset($params->email) && isset($params->name) && isset($params->surname) && isset($params->role)) {
                    $nuevoUsuario = ResUsersMapa::find($userId);
                    $nuevoUsuario->name = $params->name;
                    $nuevoUsuario->surname = $params->surname;
                    $nuevoUsuario->email = $params->email;
                    $nuevoUsuario->role = $params->role;

                    $isset_user = ResUsersMapa::where([['email', '=', $params->email], ['id', '!=', $userId]])->first();
                    $count = is_array($isset_user) ? count($isset_user) : 0;

                    if ($isset_user) {
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'Ya existe email registrado en sistema',
                        );
                    } else {
                        $nuevoUsuario->update();
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'Usuario actualizado correctamente en el sistema',
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

        } else {
            return "usuario NO autenticado";
        }
    }

    public function updatePassword(Request $request, $userId)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            Log::info('actualizando...');
            // se debe de recoger lo recibdo por post
            $json = $request->input('json', null);
            Log::info($json);
            if (!is_null($json)) {
                $params = json_decode($json);

                if (isset($params->password)) {
                    $nuevoUsuario = ResUsersMapa::find($userId);
                    $nuevoUsuario->password = hash('sha256', $params->password);
                    $nuevoUsuario->update();
                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Usuario actualizado correctamente en el sistema',
                    );
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

        } else {
            return "usuario NO autenticado";
        }
    }

    public function delete(Request $request, $userId)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            Log::info('eliminando...');

            $nuevoUsuario = ResUsersMapa::find($userId);
            if ($nuevoUsuario) {
                $nuevoUsuario->delete();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Usuario eliminado correctamente en el sistema',
                );

            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'NO existe este usuario en el sistema',
                );
            }

            return response()->json($data, 200);

        } else {
            return "usuario NO autenticado";
        }
    }

    public function login(Request $request)
    {
        $jwtAuth = new JwtAuth();

        // se debe de recoger lo recibido por post
        $json = $request->input('json', null);

        if (!is_null($json)) {
            $params = json_decode($json);
            if (isset($params->email) && isset($params->password) && isset($params->getToken)) {
                $email = $params->email;
                $password = $params->password;
                $getToken = $params->getToken;
                $pwdCifrado = hash('sha256', $password);
                $signup = $jwtAuth->signup($email, $pwdCifrado, $getToken);
                return response()->json($signup, 200);
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se puede hacer login, faltan datos',
                );
                return response()->json($data, 200);
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'No se puede hacer login, faltan datos',
            );
            return response()->json($data, 200);
        }
    }

    public function getUsersPagination(Request $request, $limit, $offset)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $usuarios = DB::select('SELECT id, name, surname, role, email
                                    from res_users_mapas where id > 1 order by id limit ' . $limit . ' offset ' . $offset);
            return $usuarios;
        } else {
            return "usuario NO autenticado";
        }
    }

    public function getUsers(Request $request)
    {
        $jwtAth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $checkToken = $jwtAth->checkToken($token);
        if ($checkToken) {
            $usuarios = DB::select('SELECT id, name, surname, role, email
                                    from res_users_mapas where id > 1 order by id ');
            return $usuarios;
        } else {
            return "usuario NO autenticado";
        }
    }

}
