<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_id',
        'question_id',
        'selected_answer',
        'is_correct',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
