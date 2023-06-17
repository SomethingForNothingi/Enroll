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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    const SCHOOL_ONE = 'SCHOOL_ONE';
    const SCHOOL_NATION = 'SCHOOL_NATION';
    const SCHOOL = [
        'SCHOOL_ONE'    => '托克托县第一中学',
        'SCHOOL_NATION' => '托克托县民族中学'
    ];

    /**
     * 选择学校
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function chooseSchool(Request $request)
    {
        // 验证
        $userInfo = Auth::guard(self::GUARD)->user();
        $card_id = $userInfo->card_id;
        $score = $userInfo['total_rank'];

        $school = $request->get('school');
        // 报名学校是否正确
        if (!in_array($school, [ self::SCHOOL_NATION, self::SCHOOL_ONE ])) {
            return response($this->returnData(self::FAIL, '传入学校错误，请传入正确key值，SCHOOL_ONE=>托克托县第一中学，SCHOOL_NATION=>托克托县民族中学'));
        }
        // 验证是否允许该时段登陆
        $canLogin = $this->validateCanOption($score);
        if (!$canLogin) {
            return response($this->returnData(self::FORBIDDEN, '超出报名时段'));
        }


        $batch = $this->getBatch($score);

        if ($school == self::SCHOOL_ONE) {
            $other_school = self::SCHOOL_NATION;
        } else {
            $other_school = self::SCHOOL_ONE;
        }

        $key = $school . '_' . $batch;
        $school2 = $other_school . '_' . $batch;

        // 从另一个学校里清除
        Redis::zrem($school2, $card_id);
        // 存入redis zset中
        Redis::zadd($key, $score, $card_id);


        $set_school = "SET_" . $school;
        $set_other_school = "SET_" . $other_school;
        // 存入set
        if (Redis::sismember($set_other_school, $card_id)) {
            Redis::smove($set_other_school, $set_school, $card_id);
        }
        Redis::sadd("SET_" . $school, $card_id);

        return response($this->returnData(self::OK));
    }

    /**
     * 返回当前排名
     */
    public function nowRank()
    {
        $userInfo = Auth::guard(self::GUARD)->user();
        $card_id = $userInfo->card_id;
        $total_rank = $userInfo['total_rank'];
        $batch = $this->getBatch($total_rank);
        if ($batch == 0) {
            return response($this->returnData(self::OK, '', []));
        }

        // 获取报名学校
        $isOne = Redis::sismember("SET_SCHOOL_ONE", $card_id);
        if ($isOne) {
            $school = self::SCHOOL_ONE;
        } else if (Redis::sismember("SET_SCHOOL_NATION", $card_id)) {
            $school = self::SCHOOL_NATION;
        } else {
            return response($this->returnData(self::OK, '', [ 'batch' => $batch - 8 ]));
        }
        $enroll_school = self::SCHOOL[$school];


        $key = $school . '_' . $batch;
        // 返回全部报名人员信息
        $rank = $this->getStudentRank($key, $card_id);
        $total = self::CAN_ENROLL[$school][$batch] ?? '';
        if (!$total) {
            return response($this->returnData(self::OK, '', []));
        }

        $count = $this->getStudentCountByKey($key);

        return response($this->returnData(self::OK, '',
            [
                'batch'      => $batch - 8,
                'total'      => $total,
                'count'      => $count,
                'rank'       => $rank,
                'school'     => $enroll_school,
                'school_key' => $school,
                'admission'  => Redis::sismember('admission', $card_id)
            ]));
    }

    /**
     * @return Application|Response|ResponseFactory
     */
    public function getUserInfo()
    {
        $data = Auth::guard(self::GUARD)->user();
        return response($this->returnData(self::OK, '获取成功', [ 'user' => $data ]));
    }

    public function getOne()
    {
        $one = Student::query()->inRandomOrder()->first();
        $data['card_id'] = $one->card_id;
        $data['password'] = strtoupper(substr($one->card_id, -6));
        return $this->returnData(self::OK, '', $data);
    }

    /**
     * 返回当前状态
     * @return array
     */
    public function status()
    {
        // 验证
        $userInfo = Auth::guard(self::GUARD)->user();
        // 验证是否允许该时段登陆
        $status = $this->validateCanOption($userInfo->total_rank);
        return $this->returnData(self::OK, '', [ 'status' => $status ]);
    }


    /**
     * 返回当前报名批次
     */
    public function getBatch($total_rank)
    {
        foreach (self::CAN_LOGIN as $k => $v) {
            if ($total_rank >= $v['min'] && $total_rank <= $v['max']) {
                return $k;
            }
        }
    }

    /**
     * 重置密码
     */
    public function reset(Request $request)
    {
        $password = $request->input('password');
        $user_id = Auth::guard(self::GUARD)->user()['id'];
        Student::query()->find($user_id)->update([ 'password' => Hash::make($password) ]);
    }
}
