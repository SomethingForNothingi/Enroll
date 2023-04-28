<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IndexController;
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

// 登陆
Route::any('login', [ AuthController::class, 'login' ]);
// 获取验证码
Route::any('getcaptcha', [ AuthController::class, 'getCaptcha' ]);
// 随机获取账号
Route::any('getup', [ IndexController::class, 'getOne' ]);

Route::group([ 'middleware' => [ 'auth:api' ] ], function (Router $router) {
    // 退出
    $router->get('logout', [ AuthController::class, 'logout' ]);
    // 报名
    $router->post('choose', [ IndexController::class, 'chooseSchool' ]);
    // 当前排名
    $router->post('getrank', [ IndexController::class, 'nowRank' ]);
    // 返回用户信息
    $router->post('getuserinfo', [ IndexController::class, 'getUserInfo' ]);
});
