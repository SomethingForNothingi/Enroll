<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class X extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'X:x';

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
        $data = Student::query()->orderByDesc('total_rank')->get()->toArray();
        foreach ($data as $k => $v) {
            $v['password'] = Hash::make(strtoupper(substr($v['idcard'], -6)));
            $v['rank'] = $k+1;
            $v['total_rank'] = $k+1;
            Student::query()->where('id', $v['id'])->update($v);
        }
    }
}
