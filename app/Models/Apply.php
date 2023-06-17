<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apply extends Model
{
    use HasFactory;
    protected $table = 'apply';
    protected $guarded = ['id'];

    public function student(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Student::class, 'card_id','card_id');
    }

    public function scopeSearch(Builder $builder, array $search): Builder
    {
        if (!empty($search['school'])) {
            $builder->where('school', $search['school']);
        }

        if (!empty($search['name'])) {
            $builder->whereHas('student', function ($query) use ($search){
                $query->where('name', 'like', '%'.$search['name'].'%');
            });
        }

        if (isset($search['apply'])) {
            $builder->where('apply', $search['apply']);
        }

        if (!empty($search['batch'])) {
            $builder->where('batch', $search['batch']);
        }

        if (!empty($search['card_id'])) {
            $builder->whereHas('student', function ($query) use ($search) {
                $query->whereRaw('card_id = ? or student_id = ?', [ $search['card_id'], $search['card_id'] ]);
            });
        }
        return $builder;
    }

    public function getFillable()
    {
        return $this->fillable;
    }
}
