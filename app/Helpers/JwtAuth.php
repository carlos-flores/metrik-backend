<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;

class JwtAuth{
  public $key;

  public function __construct(){
    $this->key = 'kenya-kain-Rapunsel-tania-carlos-9439593450238582390';
  }

  public function signup($email, $password, $getToken = true){
    Log::info($email);
    Log::info($password);
    Log::info($getToken);
    

    if ($email === 'prueba' && $password === hash('sha256','prueba')) {
      // Se genera el token y se devuelve
      $payload = array(
        'sub'=>1,
        'email'=>$email,
        'name'=>$email,
        'surname'=>$email,
        'iat'=>time(),
        'exp'=>time() + (60*60*24)
      );
      $jwt = JWT::encode($payload, $this->key ,'HS256');
      $decoded = JWT::decode($jwt, $this->key, array('HS256'));
      if ($getToken) {
        return $jwt;
      } else {
        return $decoded;
      }

    } else {
      // Se devuelve un error
      return array(
        'status'=>'error',
        'code'=>400,
        'message'=>'Login incorrecto...'
      );
    }

  }

public function signupBCK($email, $password, $getToken = true){
    Log::info($email);
    Log::info($password);
    Log::info($getToken);
    $user = User::where(array('email' => $email,'password'=>$password))->first();

    if ($user) {
      // Se genera el token y se devuelve
      $payload = array(
        'sub'=>$user->id,
        'email'=>$user->email,
        'name'=>$user->name,
        'surname'=>$user->surname,
        'iat'=>time(),
        'exp'=>time() + (60*60)
      );
      $jwt = JWT::encode($payload, $this->key ,'HS256');
      $decoded = JWT::decode($jwt, $this->key, array('HS256'));
      if ($getToken) {
        return $jwt;
      } else {
        return $decoded;
      }

    } else {
      // Se devuelve un error
      return array(
        'status'=>'error',
        'code'=>400,
        'message'=>'Login incorrecto...'
      );
    }

  }

  public function checkToken($jwt, $getIdentity = false){
    $auth = false;

    try {
      $decoded = JWT::decode($jwt, $this->key, array('HS256'));

      if (is_object($decoded) && isset($decoded->sub)) {
        $auth = true;
        if($getIdentity){
          return $decoded;
        }
      } else {
        $auth = false;
      }

    } catch (\UnexpectedValueException $e) {
      $auth = false;
    } catch(\DomainException $e){
      $auth=false;
    }
    return $auth;
  }
}
