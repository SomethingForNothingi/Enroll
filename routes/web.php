<?php

use Illuminate\Support\Facades\Route;

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

Route::any('admin_login', [ \App\Http\Controllers\AdminLoginController::class, 'login' ]);
// 获取验证码
Route::any('admin_getcaptcha', [ \App\Http\Controllers\AdminController::class, 'getCaptcha' ]);
Route::group([ 'middleware' => [ 'auth:admin' ] ], function (\Illuminate\Routing\Router $router) {
    // 管理员
    $router->any('get_columns', [\App\Http\Controllers\AdminController::class,'getColumns']);
    // 获取列表
    $router->any('get_list', [\App\Http\Controllers\AdminController::class, 'getList']);
});
