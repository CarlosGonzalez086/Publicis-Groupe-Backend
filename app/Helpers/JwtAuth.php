<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {

    public $key;

    public function __construct() {
        $this->key = 'clave_secreta-99887766';
    }

    public function signup($email, $password, $getToken = null) {
        //Buscar si existe el usuario con las credenciales
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();
        //Comprobar si son correctas
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        //Generar el token con los datos del usuario identificado
        if ($signup) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'nombre' => $user->nombre,
                'apaterno' => $user->apaterno,
                'amaterno' => $user->amaterno,
                'telefono' => $user->telefono,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'genero' => $user->genero,
                'usuario' => $user->usuario,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            //Devolver los datos decodificados o el token en funcion de un parametro
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'Login Incorrecto XXXX'
            );
        }
        return $data;
    }

    public function checkToken($jwt, $getIdentify = false) {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && $decoded->sub) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentify) {
            return $decoded;
        }
        return $auth;
    }

}

