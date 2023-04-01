<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test',[\App\Http\Controllers\IndexController::class,'test']);
Route::any('login',[\App\Http\Controllers\LoginController::class,'login']);
Route::any('choose',[\App\Http\Controllers\IndexController::class,'chooseSchool']);
Route::any('getrank',[\App\Http\Controllers\IndexController::class,'nowRank']);
Route::any('getuserandpwd',[\App\Http\Controllers\IndexController::class,'getUserAndPwd']);
