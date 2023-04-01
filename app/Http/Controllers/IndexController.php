<?php

namespace App\Http\Controllers;

use App\Jobs\EnrollPodcast;
use App\Models\Student;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    const SCHOOL_ONE = 'SCHOOL_ONE';
    const SCHOOL_NATION = 'SCHOOL_NATION';
    const SCHOOL = [
        'SCHOOL_ONE' => '托克托县第一中学',
        'SCHOOL_NATION' => '托克托县民族中学'
    ];
    // Demo 测试使用
    public function test(Request $request)
    {
        dd(date('G'));
        $user_info = $request->cookie('userInfo');
        dd(json_decode($user_info));
//        $obj = new \stdClass();
//        $obj->name = '宋文博';
//        $obj->card = '15232119961001';
//        for ($i =0;$i<20;$i++) {
//            Redis::incr('counter');
//            dispatch(new EnrollPodcast($obj));
//        }
//        $redis = Redis::connection('redis');
//        Redis::set('name', 'Taylor');
    }

    /**
     *
     * @param Request $request
     * @return bool
     */
    public function cookieValidate(): bool
    {
        if(!Cookie::has('userInfo')) {
            return false;
        }
        return true;
    }

    /**
     * 选择学校
     * @param Request $request
     * @param Student $studentObj
     * @return Application|ResponseFactory|Response
     */
    public function chooseSchool(Request $request,Student $studentObj)
    {
        if(!$this->cookieValidate()) {
            return response($this->returnData(0,'非登陆状态，无法操作'));
        }
        // 验证
        $userInfo = json_decode(Cookie::get('userInfo'));
        $card_id = $userInfo->card_id;
        $school = $request->get('school');
        // 报名学校是否正确
        if(!in_array($school,[self::SCHOOL_NATION,self::SCHOOL_ONE])) {
            return response($this->returnData(0,'传入学校错误，请传入正确key值，SCHOOL_ONE=>托克托县第一中学，SCHOOL_NATION=>托克托县民族中学'));
        }
        // 验证是否允许该时段登陆
        $canLogin = $this->validateCanOption($userInfo->total_rank);
        if(!$canLogin) {
            return response($this->returnData(2,'超出报名时段'));
        }

        //报名检测，如果报名了其他学校，则取消该学校报名
        if($school == self::SCHOOL_ONE) {
            $other_school = self::SCHOOL_NATION;
        } else {
            $other_school = self::SCHOOL_ONE;
        }
        // 拼接时间
        $h = date('G');
        $key = $school.'_'.$h;
        $other_school .= '_'.$h;
        Redis::zrem($other_school,$card_id);
        $score = $userInfo->total_score;
        // 存入redis中
        Redis::zadd($key,$score,$card_id);

        // 返回全部报名人员信息
        $rank = $this->getStudentRank($key,$card_id);
        //TODO 需要加入统招人数（根据当前时间，由计算公式所得）,测试所需
        if(!isset(self::CAN_ENROLL[$school][$h])) {
            $total = 100;
        } else {
            $total = self::CAN_ENROLL[$school][$h];
        }
        $count = $this->getStudentCountByKey($key);

        return response($this->returnData(1,self::SCHOOL[$school].'统招'.$total.'人，共计'.$count.'人报名，当前排名第'.$rank));
    }

    /**
     * 返回当前排名
     */
    public function nowRank(Request $request,Student $studentObj) {
        if(!$this->cookieValidate()) {
            return response($this->returnData(0,'非登陆状态，无法操作'));
        }
        $userInfo = json_decode(Cookie::get('userInfo'));
        $card_id = $userInfo->card_id;
        $school = $request->get('school');
        // 报名学校是否正确
        if(!in_array($school,[self::SCHOOL_NATION,self::SCHOOL_ONE])) {
            return response($this->returnData(0,'传入学校错误，请传入正确key值，SCHOOL_ONE=>托克托县第一中学，SCHOOL_NATION=>托克托县民族中学'));
        }
        // 获取学生信息
        $studentInfo = $studentObj->getInfoByCard($card_id);
        if(empty($studentInfo)) {
            return response($this->returnData(0,'请输入正确的准考证号'));
        }

        $h = date('G');
        $key = $school.'_'.$h;
        // 返回全部报名人员信息
        $rank = $this->getStudentRank($key,$card_id);
        //TODO 需要加入统招人数（由计算公式所得）
        $total = self::CAN_ENROLL[$school][$h];
        $count = $this->getStudentCountByKey($key);

        return response($this->returnData(1,'统招'.$total.'人，共计'.$count.'人报名，当前排名第'.$rank));
    }

    /**
     * 登陆测试用
     * @param Student $student
     * @return Application|ResponseFactory|Response
     */
    public function getUserAndPwd(Student $student)
    {
        $user = $student->getTest();
        $userInfo['student_id'] = $user->student_id;
        $userInfo['card_id'] = $user->card_id;
        $userInfo['name'] = $user->name;
        $userInfo['sex'] = $user->sex;
        $userInfo['nation'] = $user->nation;
        $userInfo['apply'] = $user->apply;

        return response($this->returnData(1,'登陆成功',$userInfo))->cookie('userInfo',$user);

    }

}
