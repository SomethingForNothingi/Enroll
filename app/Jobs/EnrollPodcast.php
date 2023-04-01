<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EnrollPodcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $obj;

    /**
     * Create a new job instance.
     * @param \stdClass $obj 对象
     */
    public function __construct(\stdClass $obj)
    {
        $this->obj = $obj;
    }

    /**
     * Execute the job.
     * 消费队列，对报名学生进行出栈
     *
     * @return void
     */
    public function handle()
    {
        // 队列出栈处理
        print_r($this->obj);
    }
}
