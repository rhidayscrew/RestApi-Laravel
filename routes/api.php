<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'v1','middleware' => 'cors'], function(){

    Route::resource('meeting','MeetingController',[
        'except' =>['create','edit']  //axcept yaitu tidak akan menggunakan/memerlukan halaman  crate dan edit
    ]);

    Route::resource('meeting/registration','RegisterController', [
        'only'=>['store', 'destroy'] // hanya membutuhkan store dan destroy. karena hanya menggukankan configurasi membuat(store) registrasi miting atau membatalkan nya (destroy)
    ]);

    Route::post ('/user/register', [
        'uses' => 'AuthController@store'  // diarahkan ke authconroler cek coba
    ]);

    Route:: post ('/user/signin',[
        'uses' => 'AuthController@signin'  // diarahkan ke authconroler cek coba
    ]);

});