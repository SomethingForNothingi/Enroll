<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Models\Apply;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

// Redis数据同步数据库
class SyncData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('民族学校数据同步---');
        $this->syncData('SCHOOL_NATION');
        $this->info('民族学校数据同步结束---');

        $this->info('一中数据同步---');
        $this->syncData('SCHOOL_ONE');
        $this->info('一中数据同步结束---');
        return 0;
    }

    /**
     * 数据同步逻辑
     * @return void
     */
    public function syncData(string $school)
    {
        if ($school == 'SCHOOL_ONE') {
            $apply = '一中';
        } else {
            $apply = '民中';
        }
        $controller = new Controller();
        $batch = $controller::CAN_ENROLL[$school];
        $data = [];
        foreach ($batch as $k => $v) {
            $key = $school . '_' . $k;
            $data[$k] = Redis::zrevrange($key, 0, -1);
        }
        // 获取所有学号
        $dataFilter = collect($data)->collapse();

        $studentData = Student::query()->whereIn('card_id',$dataFilter)->get()->toArray();
        $field = (new Apply())->getFillable();
        $insert = [];
        foreach ($studentData as $k => $v)
        {
            foreach ($field as $v2) {
                $insert[$k][$v2] = $v[$v2];
            }
            $insert[$k]['apply'] = $apply;
        }
        dd($insert);

    }
}
