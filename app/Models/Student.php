<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'student';

    /**
     * 根据学号或者准考证号找到学生信息
     * @param $card
     * @return Builder|Model|object|null
     */
    public function getInfoByCard($card) {
        return self::query()->where('card_id',$card)->orWhere('student_id',$card)->first();
    }

    public function getTest() {
        return self::query()->orderByRaw('rand()')->first();
    }




}
