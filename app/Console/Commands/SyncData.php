<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        $this->syncData('NATION');
        $this->info('民族学校数据同步结束---');

        $this->info('一中数据同步---');
        $this->syncData('ONE');
        $this->info('一中数据同步结束---');
        return 0;
    }

    /**
     * 数据同步逻辑
     * @return void
     */
    public function syncData(string $school)
    {

    }
}
