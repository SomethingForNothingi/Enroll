<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function login(Request $request)
    {
        $params = $request->all();
        // 验证码
        $captcha = $this->validateCaptcha($params['key'], $params['captcha']);
        if (!$captcha) {
            return response($this->returnData(self::FAIL, '验证码错误'));
        }
        //验证
        $credentials = [
            'username' => $params['username'],
            'password' => $params['password']
        ];
        $token = Auth::guard(self::GUARD_ADMIN)->attempt($credentials);
        if (!$token) {
            return $this->returnData(self::FAIL, '账户信息错误');
        }

        $userData = Auth::guard(self::GUARD_ADMIN)->user();

        return $this->returnData(self::OK, '登陆成功', [ 'token' => $token, 'user' => $userData ]);
    }

    public function logout(): array
    {
        Auth::guard(self::GUARD_ADMIN)->logout();
        return $this->returnData(self::OK);
    }

    /**
     * 获取验证码
     */
    public function getCaptcha()
    {
        return response(
            $this->returnData(self::OK, '获取验证成功', app('captcha')->create('default', true))
        );

    }

    /**
     * 验证验证码
     */
    public function validateCaptcha($key, $captcha): bool
    {
        if (captcha_api_check($captcha, $key)) {
            return true;
        } else {
            return false;
        }
    }
}
