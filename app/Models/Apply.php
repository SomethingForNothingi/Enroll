<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apply extends Model
{
    use HasFactory;
    protected $table = 'apply';
    protected $fillable = [ 'card_id', 'apply', 'batch', 'batch_rank'];

    public function getFillable()
    {
        return $this->fillable;
    }
}
