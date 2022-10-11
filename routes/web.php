<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Middleware\ApiAuthMiddleware;
Route::get('/', function () {
    return view('welcome');
});
//Rutas del API
        /*
         * GET: CONEGUIR DATOS O RECURSOS
         * POST: GUARDAR DATOS O RECURSOS O HACER LOGICA DESDE UN FORMULARIO
         * PUT:ACTUALIZAR DATOS O RECURSOS
         * DELETE:ELIMINAR DATOS O RECURSOS
         */
//Rutas del controlador de usuarios
        Route::post('/api/register','UserController@register');
        Route::post('/api/login','UserController@login');
        Route::put('/api/user/update','UserController@update');
        Route::get('/api/user/detail/{id}','UserController@detail');
        Route::resource('/api/user', 'UserController');
//Rutas del controlador de categorias
        Route::resource('/api/category', 'CategoryController');

//Rutas del controlador de post   
        Route::resource('/api/mimusica', 'MimusicaController');
        Route::get('/api/mimusica/user/{id}','Mimusicacontroller@getMimusicaByUser');
        Route::get('/api/mimusica/category/{id}','MimusicaController@getMimusicaByCategory');