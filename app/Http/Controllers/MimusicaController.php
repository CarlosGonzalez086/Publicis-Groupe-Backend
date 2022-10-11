<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Mimusica;
use App\Helpers\JwtAuth;

class MimusicaController extends Controller
{
       public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getMimusicaByCategory', 'getMimusicaByUser']]);
    }

    public function index() {

        $mimusica = Mimusica::all()->load('category');

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'mimusicas' => $mimusica
                        ], 200);
    }

    public function show($id) {
        $mimusica = Mimusica::find($id)->load('category')->load('user');

        if (is_object($mimusica)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Post obtenido.',
                'post' => $mimusica
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe el Post.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Recogemos los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Conseguir usuario identificado
            $user = $this->getIndentity($request);
            //Validamos los datos 
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'author' => 'required',
                        'category_id' => 'required',
                
            ]);
            if ($validate->fails()) {
                //La validacion a fallado
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El Post no se ha guardado',
                );
            } else {
                //Guardamos el post
                $mimusica = new Mimusica();
                $mimusica->user_id = $user->sub;
                $mimusica->category_id = $params->category_id;
                $mimusica->name = $params->name;
                $mimusica->author = $params->author;
                $mimusica->save();
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El Post se ha guardado',
                    'category' => $mimusica
                );
            }
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviando ningun dato.Intente otra vez'
            );
        }
        //Devolver resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        //Datos para devolver
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Te faltan mas datos para actualizar el post.Intente otra vez'
        );
        if (!empty($params_array)) {
            //Validamos los datos 
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'author' => 'required',
                        'category_id' => 'required'
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            //Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            //Conseguir usuario identificado
            $user = $this->getIndentity($request);

            //Conseguir el registro     
            $mimusica = Mimusica::where('id', $id)->where('user_id', $user->sub)->first();
            if (!empty($mimusica) && is_object($mimusica)) {

                //Actualizar el registro  
                $mimusica->update($params_array);
                //Deolver algo
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'El post se ha actualizado con exito.',
                    'post' => $mimusica,
                    'changes' => $params_array
                );
            }           
        }
        //Devolver los datos  
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        //Conseguir usuario identificado
        $user = $this->getIndentity($request);

        //Comprobar si existe el registro
        $mimusica = Mimusica::where('id', $id)->where('user_id', $user->sub)->first();
        if (!empty($mimusica)) {
            //Borrarlo
            $mimusica->delete();
            //Devolver algo
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $mimusica
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe el post'
            ];
        }
        return response()->json($data, $data['code']);
    }
    
    
    public function getMimusicaByCategory($id) {
        $mimusica = Mimusica::where('category_id', $id)->get();
        
        return response()->json([
            'status' => 'success',
            'mimusica' => $mimusica
        ],200);
    }
    
    public function getMimusicaByUser($id) {
        $mimusica = Mimusica::where('user_id', $id)->get();
        
        return response()->json([
            'status' => 'success',
            'mimusica' => $mimusica
        ],200);
    }

    private function getIndentity($request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    } 
}
