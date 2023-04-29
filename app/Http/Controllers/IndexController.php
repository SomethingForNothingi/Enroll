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
        $school = $request->get('school');
        // 报名学校是否正确
        if (!in_array($school, [ self::SCHOOL_NATION, self::SCHOOL_ONE ])) {
            return response($this->returnData(self::FAIL, '传入学校错误，请传入正确key值，SCHOOL_ONE=>托克托县第一中学，SCHOOL_NATION=>托克托县民族中学'));
        }
        // 验证是否允许该时段登陆
        $canLogin = $this->validateCanOption($userInfo->total_rank);
        if (!$canLogin) {
            return response($this->returnData(self::FORBIDDEN, '超出报名时段'));
        }

        if ($school == self::SCHOOL_ONE) {
            $other_school = self::SCHOOL_NATION;
        } else {
            $other_school = self::SCHOOL_ONE;
        }
        // 批次
        $batch = $this->getBatch();

        $key = $school . '_' . $batch;
        $school2 = $other_school . '_' . $batch;

        // 从另一个学校里清除
        Redis::zrem($school2, $card_id);
        // 存入redis zset中
        $score = $userInfo->total_score;
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

        // 获取报名学校
        $isOne = Redis::sismember("SET_SCHOOL_ONE", $card_id);
        if ($isOne) {
            $school = self::SCHOOL_ONE;
        } else if(Redis::sismember("SET_SCHOOL_NATION", $card_id)){
            $school = self::SCHOOL_NATION;
        } else {
            return response($this->returnData(self::FAIL, '', []));
        }
        $enroll_school = self::SCHOOL[$school];

        $batch = $this->getBatch();
        $key = $school . '_' . $batch;
        // 返回全部报名人员信息
        $rank = $this->getStudentRank($key, $card_id);
        //TODO 需要加入统招人数（由计算公式所得）
        $total = self::CAN_ENROLL[$school][$batch];

        $count = $this->getStudentCountByKey($key);

        return response($this->returnData(self::OK, '',
            [
                'total'  => $total,
                'count'  => $count,
                'rank'   => $rank,
                'school' => $enroll_school,
                'school_key' => $school
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
        $data['password'] = strtoupper(substr($one->idcard, -6));
        return $this->returnData(self::OK, '', $data);
    }


    /**
     * 返回当前报名批次
     */
    public function getBatch(): int
    {
        return date('G') % 2 + 1;
    }
}
