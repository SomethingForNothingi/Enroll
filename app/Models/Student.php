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
    protected $guarded = ['id'];


    public function scopeSearch(Builder $builder, array $search): Builder
    {
        if (!empty($search['name'])) {
            $builder->where($this->table.'.name', 'like', '%'.$search['name'].'%');
        }

        if (isset($search['apply'])) {
            $builder->where('apply.apply', $search['apply']);
        }

        if (!empty($search['batch'])) {
            $builder->where('apply.batch', $search['batch']);
        }

        if (!empty($search['card_id'])) {
            $builder->where('apply.card_id', 'like','%'.$search['card_id'].'%');
        }
        return $builder;
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
