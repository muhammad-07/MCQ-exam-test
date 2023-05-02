<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'key', 'value'
    ];

    public function getTimeLimitAttribute()
    {
        return $this->where('key', 'time_limit')->first()->value;
    }
}
