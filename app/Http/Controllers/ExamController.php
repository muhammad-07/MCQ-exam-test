<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Exam;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function start(Candidate $candidate)
    {
        $questions = Question::inRandomOrder()->take($this->getQuestionCount())->get();

        $examSettings = $this->getExamSettings();

        $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
        $endTime = Carbon::now()->addMinutes($timeLimit);

        return view('exam.start', compact('candidate', 'questions', 'endTime'));
    }

    public function submit(Request $request, Candidate $candidate)
    {
        $selectedAnswers = $request->input('selected_answers', []);
        $examIds = $request->input('exam_ids', []);

        $exams = Exam::whereIn('id', $examIds)->get();

        foreach ($exams as $exam) {
            $exam->selected_answer = $selectedAnswers[$exam->id] ?? null;
            $exam->is_correct = $exam->selected_answer == $exam->question->correct_answer;
            $exam->save();
        }

        $correctCount = $exams->where('is_correct', true)->count();
        $wrongCount = $exams->where('is_correct', false)->count();
        $totalCount = $correctCount + $wrongCount;
        $score = ($correctCount / $totalCount) * 100;

        return view('exam.result', compact('correctCount', 'wrongCount', 'totalCount', 'score'));
    }

    private function getQuestionCount()
    {
        $questionCount = $this->getExamSettings()['question_count'] ?? 10;
        return min(max($questionCount, 1), 50); // limit between 1 and 50
    }

    private function getExamSettings()
    {
        $examSettings = [];

        foreach (\App\Models\ExamSetting::all() as $setting) {
            $examSettings[$setting->name] = $setting->value;
        }

        return $examSettings;
    }
}
