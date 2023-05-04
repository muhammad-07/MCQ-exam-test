<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name', 'value'
    ];

    public function getTimeLimitAttribute()
    {
        return $this->where('name', 'time_limit')->first()->value;
    }
}
