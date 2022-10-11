<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use App\Helpers\JwtAuth;

class UserController extends Controller
{
     public function pruebasuser(Request $request) {
        return "Accion de pruebas de USER-CONTROLLER";
    }
       public function __construct() {
        $this->middleware('api.auth', ['except' => ['register', 'login','update','detail','store','index','create','show']]);
    }

    public function register(Request $request) {
        
        //Recoger los datos del usuario por post
        $json = $request->input('json', null);
        //Decodificar los datos
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //Validacion por medio de un if
        if (!empty($params) && !empty($params_array)) {
            //Limpiar datos
            $params_array = array_map('trim', $params_array);
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'email' => 'required|email|unique:users', //Comprobar si el usuario existe  
                        'nombre' => 'required|alpha',
                        'apaterno' => 'required|alpha',
                        'amaterno' => 'required|alpha',
                        'telefono' => 'required',
                        'fecha_nacimiento' => 'required',
                        'genero' => 'required|alpha',
                        'usuario' => 'required|unique:users',
                        'password' => 'required'
            ]);
            if ($validate->fails()) {
                //La validacion a fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                //Validacion correctamente
                //Cifrar la contraseña
                $pwd = hash('sha256', $params->password);
                //Creal el usuario
                $user = new User();
                $user->email = $params_array['email'];
                $user->nombre = $params_array['nombre'];
                $user->apaterno = $params_array['apaterno'];
                $user->amaterno = $params_array['amaterno'];
                $user->telefono = $params_array['telefono'];
                $user->fecha_nacimiento = $params_array['fecha_nacimiento'];
                $user->genero = $params_array['genero'];
                $user->usuario = $params_array['usuario'];
                $user->password = $pwd;
                $user->rol = 'Usuario_Normal';
                //Guardar el usuario
                $user->save(); //Realiza un insert into a la base de datos automaticamente
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos no son correctos.Intente otra vez',
            );
        }
        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {
        
        $jwtAuth = new \JwtAuth();
        //Recibbir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        //Validar esos datos
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email', //Comprobar si el usuario existe    
                    'password' => 'required'
        ]);
        if ($validate->fails()) {
            //La validacion a fallado
            $signup = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha podido indentificar',
                'errors' => $validate->errors()
            );
        } else {
            //Cifrar la password
            $pwd = hash('sha256', $params->password);
            //Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd);
            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        
        //Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);
        //Recoger datos por post            
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if ($checkToken && !empty($params_array)) {
            //Actualizar el usuario                       
            //Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            //Validar los datos
            
            $validate = \Validator::make($params_array, [
                        'nombre' => 'required|alpha',
                        'apaterno' => 'required|alpha',
                        'amaterno' => 'required|alpha',
                        'telefono' => 'required',
                        'genero' => 'required|alpha',
                        'usuario' => 'required|unique:users',
                        'email' => 'required|email|unique:users' . $user->sub //Comprobar si el usuario existe                     
            ]);
            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['rol']);
            unset($params_array['fecha_nacimiento']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['token']);
            //Actualizar el usuario en la base de datos
            $user_update = User::where('id', $user->sub)->update($params_array);
            //Devolver array con resultados
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El usuario se ha actualizado con exito.',
                'user' => $user_update,
                'Usuario Viejo' => $user,
                'Usuario Actualizado' => $params_array
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta indentificado.'
            );
        }
        return response()->json($data, $data['code']);
    }


    public function detail($id) {
        
        $user = User::find($id);
        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Usuario obtenido.',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Este usuario no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
      public function destroy($email, Request $request) {
        //Conseguir usuario identificado
        $user = $this->getIndentity($request);

        //Comprobar si existe el registro
        $user = User::where('email', $email)->first();
        if (!empty($user)) {
            //Borrarlo
            $user->delete();
            //Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe el usuario'
            ];
        }
        return response()->json($data, $data['code']);
    }
     private function getIndentity($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    } 
     
}
