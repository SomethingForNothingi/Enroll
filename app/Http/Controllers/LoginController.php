<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // 登陆
    public function login(Request $request,Student $student)
    {

        $card = $request->post('card_id');
        $pwd = strtoupper($request->post('password'));

        // 输入验证
        $validator = Validator::make($request->post() , [
            'card_id'  =>  'required',
            'password'  =>  'required',
//            'captcha'  =>  'required',
        ] ,[]);
        if($validator->fails()) {
            return response($this->returnData(0,'请输入账号/密码'));
        }


        // 获取学生信息
        $user = $student->getInfoByCard($card);
        if(!$user) {
            return response($this->returnData(0,'准考证号/考生号错误，请重新输入'));
        }

        // 获取密码
        $passwordTrue = strtoupper(substr($user->idcard,-6));
        if($pwd != $passwordTrue) {
            return response($this->returnData(0,'密码错误'));
        }

        // 验证是否允许该时段登陆
        $canLogin = $this->validateCanOption($user->total_rank);
        if(!$canLogin) {
            return response($this->returnData(2,'超出报名时段'));
        }

        $userInfo['student_id'] = $user->student_id;
        $userInfo['card_id'] = $user->card_id;
        $userInfo['name'] = $user->name;
        $userInfo['sex'] = $user->sex;
        $userInfo['nation'] = $user->nation;
        $userInfo['apply'] = $user->apply;

        // cookie时间
//        $minute = date('i');
//        $limit = 60 - $minute;

//        return response($this->returnData(1,'登陆成功',$userInfo))->cookie('userInfo',$user,$limit);
        return response($this->returnData(1,'登陆成功',$userInfo))->cookie('userInfo',$user);
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
    public function validateCaptcha(Request $request): bool
    {
        $captcha = $request->input('captcha'); //验证码
        $key = $request->input('key'); //key
        if (captcha_api_check($captcha , $key)){
            return true;
        } else {
            return false;
        }
    }
}
