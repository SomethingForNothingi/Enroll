<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public const FAIL = 0;
    public const OK = 1;
    public const FORBIDDEN = 2;
    public const GUARD = 'api';
    public const GUARD_ADMIN = 'admin';


    /**
     * 初始化
     */
//    public function __construct() {
//        date_default_timezone_set("PRC");
//    }

    // 学校可报名人数(按时间)
    public const CAN_ENROLL = [
        // 一中
        'SCHOOL_ONE'    => [
            8 => 60,
            9 => 75,
            10 => 75,
            11 => 76,
            12 => 74,
            13 => 226,
            14 => 265,
            15 => 330,
        ],
        // 民族
        'SCHOOL_NATION' => [
            8 => 40,
            9 => 41,
            10 => 39,
            11 => 40,
            12 => 40,
            13 => 115,
            14 => 142,
            15 => 167,
        ],
    ];

    // 学生排名可登陆登陆时间段
    public const CAN_LOGIN = [
        8  => [
            'min' => 1,
            'max' => 100
        ],
        9  => [
            'min' => 101,
            'max' => 216
        ],
        10 => [
            'min' => 217,
            'max' => 330
        ],
        11 => [
            'min' => 331,
            'max' => 446
        ],
        12 => [
            'min' => 447,
            'max' => 560
        ],
        13 => [
            'min' => 561,
            'max' => 901
        ],
        14 => [
            'min' => 902,
            'max' => 1308
        ],
        15 => [
            'min' => 1309,
            'max' => 2000
        ],
    ];

    /**
     * @param $code int 0错误，1失败
     * @param $msg string 返回提示信息
     * @param $data array 返回数据
     * @return array
     */
    public function returnData(int $code, string $msg = '', array $data = []): array
    {
        return [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
    }

    /**
     * 查看当前时间是否允许登陆
     * @param $rank
     * @return bool
     */
    public function validateCanOption($rank): bool
    {
        //TODO 记得注掉
//        return true;
        //TODO 日期检测，记得核对确认
//        if (date('Y-m-d') == '2023.06.01') {
//            return false;
//        }

        $hour = date('G');
        if (!isset(self::CAN_LOGIN[$hour])) {
            return false;
        }
        $interval = self::CAN_LOGIN[$hour];

        if (empty($interval)) {
            return false;
        }
        $min = $interval['min'];
        $max = $interval['max'];

        if ($rank >= $min && $rank <= $max) {
            return true;
        }

        return false;
    }

    /**
     * 返回当前报名人数
     * @param $key
     * @return int
     */
    function getStudentCountByKey($key): int
    {
        return Redis::zcard($key);
    }

    /**
     * 根据考号返回学生当前排名
     * @param $key string|int key
     * @param $card_id string|int 考生号
     * @return int 排名
     */
    function getStudentRank($key, $card_id): int
    {
        $scoreInfo = Redis::zrevrange($key, 0, -1, [ 'withscores' => true ]);
        $cards = array_keys($scoreInfo);
        $rank = array_search($card_id, $cards);
        return $rank + 1;
    }
}
