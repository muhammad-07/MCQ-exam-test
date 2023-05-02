<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Exam;
use App\Models\ExamSetting;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    // public function start(Candidate $candidate)
    // {
    //     $question = Question::paginate(10);
    //     // dd($question);
    //     $examSettings = $this->getExamSettings();

    //     $questionNumber = $totalQuestions = 10;

    //     $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
    //     $endTime = Carbon::now()->addMinutes($timeLimit);

    //     return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    // }
    public function start(Request $request, Candidate $candidate)
    {
        
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);


        $candidate = Candidate::firstOrCreate(
            $validatedData
        );



        $question = Question::inRandomOrder()->take($this->getQuestionCount())->first();
        // dd($question);
        $examSettings = $this->getExamSettings();

        $questionNumber = $totalQuestions = 10;

        $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
        $endTime = Carbon::now()->addMinutes($timeLimit);

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    }
/*
    public function startTest(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');

        $questions = Question::inRandomOrder()->limit(10)->get();
        $negative_score = ExamSetting::where('key', 'negative_score')->first()->value;

        $time_limit = ExamSetting::where('key', 'time_limit')->first()->value;
        $time_remaining = $time_limit * 60;

        $candidate = Candidate::create([
            'name' => $name,
            'email' => $email
        ]);

        foreach ($questions as $question) {
            $candidate_answer = CandidateAnswer::create([
                'candidate_id' => $candidate->id,
                'question_id' => $question->id,
                'selected_option' => null
            ]);
        }

        return view('test', compact('questions', 'time_remaining', 'negative_score', 'candidate'));
    }*/

    public function answer(Request $request)
    {
        $name = $request->input('name');
        $answer = $request->input('answer');
        // foreach ($questions as $question) {
            $candidate_answer = Exam::create([
                'candidate_id' => $request->candidate_id,
                'question_id' => $request->question_id,
                'selected_answer' => $request->answer,
                'is_correct' => 1 //$request->is_correct,
            ]);
        // }
        $candidate = Candidate::find($request->candidate_id);
        $question = Question::inRandomOrder()->take($this->getQuestionCount())->first();
        // dd($question);
        $examSettings = $this->getExamSettings();

        $questionNumber = $totalQuestions = 10;

        $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
        $endTime = Carbon::now()->addMinutes($timeLimit);

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
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
