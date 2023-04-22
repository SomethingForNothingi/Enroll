<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class Student extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    protected $table = 'student';
    protected $fillable = ['password'];

    public const UPDATED_AT = false;

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
    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
