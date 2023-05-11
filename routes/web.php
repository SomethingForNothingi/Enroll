<?php

use App\Http\Controllers\AdminController;
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

Route::any('login', [ \App\Http\Controllers\AdminLoginController::class, 'login' ]);
// 获取验证码
Route::any('getcaptcha', [ AdminController::class, 'getCaptcha' ]);
Route::group([ 'middleware' => [ 'auth:admin' ] ], function (\Illuminate\Routing\Router $router) {
    // 管理员
    $router->any('get_columns', [ AdminController::class,'getColumns']);
    // 获取列表
    $router->any('get_list', [ AdminController::class, 'getList']);
    // 破格录取
    $router->post('admission',[ AdminController::class,'admission' ]);
});
