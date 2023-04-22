<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class   AuthController extends Controller
{
    CONST GUARD = 'api';

    public function login(Request $request)
    {
        $params = $request->all();
        // 验证码
        $captcha = $this->validateCaptcha($params['key'], $params['captcha']);
        if(!$captcha) {
            return response($this->returnData(self::FAIL,'验证码错误'));
        }
        //验证
        $credentials =[
            'card_id' => $params['card_id'],
            'password' => $params['password']
        ];
        $token = Auth::guard(self::GUARD)->attempt($credentials);

        if (!$token) {
            return $this->returnData(self::FAIL,'账户信息错误');
        }

        $userData = Auth::guard(self::GUARD)->user();

        // 验证是否允许该时段登陆
        $canLogin = $this->validateCanOption($userData->total_rank);
        if(!$canLogin) {
            return response($this->returnData(self::FORBIDDEN,'超出报名时段'));
        }

        return $this->returnData(self::OK, '登陆成功', ['token' => $token, 'user' => $userData]);
    }

    public function logout()
    {
        Auth::guard(self::GUARD)->logout();
        return $this->returnData(self::FORBIDDEN);
    }

    /**
     * 获取验证码
     */
    public function getCaptcha()
    {
        return response(
            $this->returnData(1,'获取验证成功',app('captcha')->create('default', true))
        );

    }
    /**
     * 验证码
     */
    public function validateCaptcha($key , $captcha): bool
    {
        if (captcha_api_check($captcha , $key)){
            return true;
        } else {
            return false;
        }
    }
}
