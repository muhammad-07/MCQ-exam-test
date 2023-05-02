<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
