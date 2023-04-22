<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Routing\Router;


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

Route::any('login',[AuthController::class,'login']);


Route::group(['middleware' => ['auth:api']], function (Router $router) {
    // 退出
    $router->get('logout', [AuthController::class, 'logout']);

});

Route::get('test',[\App\Http\Controllers\IndexController::class,'test']);
Route::any('choose',[\App\Http\Controllers\IndexController::class,'chooseSchool']);
Route::any('getrank',[\App\Http\Controllers\IndexController::class,'nowRank']);
Route::any('getuserandpwd',[\App\Http\Controllers\IndexController::class,'getUserAndPwd']);
Route::any('getcaptcha',[\App\Http\Controllers\LoginController::class,'getCaptcha']);
Route::any('userinfo',[\App\Http\Controllers\IndexController::class,'getUserInfo']);
