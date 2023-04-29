<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use App\Models\Apply;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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
        // 插入数据
        foreach ($data as $batch_key => $batch_val)
        {
            $insert = [];
            // 一批人
            foreach ($batch_val as $rank => $card_id) {
                $insert[$rank]['batch'] = $batch_key;
                $insert[$rank]['batch_rank'] = $rank + 1;
                $insert[$rank]['card_id'] = $card_id;
                $insert[$rank]['apply'] = $apply;
            }
            // 插入数据库
            $this->info('第'.$batch_key.'批次数据同步，共计'.count($insert).'人');

            $insert_data = array_chunk($insert, 50);
            foreach ($insert_data as $v) {
                try {
                    DB::beginTransaction();
                    Apply::query()->insert($v);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error('Mysql插入错误');
                    $this->error($e->getMessage());
                }
            }
        }
    }
}
