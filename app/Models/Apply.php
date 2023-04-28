<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apply extends Model
{
    use HasFactory;
    protected $table = 'apply';
    protected $fillable = ['student_id', 'card_id', 'name', 'sex', 'nation', 'idcard', 'apply', 'rank'];

    public function getFillable()
    {
        return $this->fillable;
    }
}
