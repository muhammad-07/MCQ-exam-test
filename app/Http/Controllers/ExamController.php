<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Exam;
use App\Models\ExamSetting;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class ExamController extends Controller
{
    
    public function start(Request $request, Candidate $candidate)
    {

        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);


        $candidate = Candidate::firstOrCreate(
            $validatedData
        );

        $questionNumber = $this->getTotalAnswered($candidate);
        $totalQuestions = $this->getQuestionCount();


        // return view('result', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));

        // $question = Question::inRandomOrder()->take($this->getQuestionCount())->first();

        // DISPLAY QUESTION
        $question = Question::whereNotIn('id', function ($query) use ($candidate) {
            $query->select('question_id')->from('exams')->where('candidate_id', $candidate->id);
        })->inRandomOrder()->first();

        // dd($question);

        if ($questionNumber == $totalQuestions || isNull($question)) {
            Exam::where('candidate_id', $candidate->id)->join('questions', '');

            $results = Exam::join('questions', 'exams.candidate_id', '=', 'questions.id')
                ->get(['questions.correct_answer', 'exams.*']);

            $correctAnswers = 0;
            $answered = count($results);
            // $totalQuestions = $this->getQuestionCount();
            foreach ($results as $result) {
                if ($result->selected_answer == $result->correct_answer)
                    $correctAnswers++;
            }
            $percentageScore = ($correctAnswers / $totalQuestions) * 100;
            $wrongAnswers = $totalQuestions - $correctAnswers;
            $unansweredQuestions = $totalQuestions - $answered;
            return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
        }

        $examSettings = $this->getExamSettings();



        $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
        $endTime = Carbon::now()->addMinutes($timeLimit);

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    }


    // SAVE ANSWER
    public function answer(Request $request)
    {
        $name = $request->input('name');
        $answer = $request->input('answer');


        $candidate_answer = Exam::updateOrCreate( // So that user can change his answer
            [
                'candidate_id' => $request->candidate_id,
                'question_id' => $request->question_id,
            ],
            [
                'candidate_id' => $request->candidate_id,
                'question_id' => $request->question_id,
                'selected_answer' => $request->answer,
                'is_correct' => 1 //$request->is_correct,
            ]
        );



        $candidate = Candidate::find($request->candidate_id);
        // $question = Question::inRandomOrder()->take($this->getQuestionCount())->first();
        // dd($question);


        $question = Question::whereNotIn('id', function ($query) use ($candidate) {
            $query->select('question_id')->from('exams')->where('candidate_id', $candidate->id);
        })->inRandomOrder()->first();

        $examSettings = $this->getExamSettings();

        $questionNumber = $this->getTotalAnswered($candidate);
        $totalQuestions = $this->getQuestionCount();

        if ($questionNumber == $totalQuestions || empty($question)) {
            // $this->getResult($candidate);
            // return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
            Exam::where('candidate_id', $candidate->id)->join('questions', '');

            $results = Exam::join('questions', 'exams.candidate_id', '=', 'questions.id')
                ->get(['questions.correct_answer', 'exams.*']);

            $correctAnswers = 0;
            $answered = count($results);
            // $totalQuestions = $this->getQuestionCount();
            foreach ($results as $result) {
                if ($result->selected_answer == $result->correct_answer)
                    $correctAnswers++;
                echo "<br>";
            }
            $percentageScore = ($correctAnswers / $totalQuestions) * 100;
            $wrongAnswers = $totalQuestions - $correctAnswers;
            $unansweredQuestions = $totalQuestions - $answered;
            return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
        }

        // return view('result', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));

        $timeLimit = $examSettings['time_limit'] ?? 10; // in minutes
        $endTime = Carbon::now()->addMinutes($timeLimit);

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    }


    public function getResult($candidate)
    {
        Exam::where('candidate_id', $candidate->id)->join('questions', '');

        $results = Exam::join('questions', 'exams.candidate_id', '=', 'questions.id')
            ->get(['questions.correct_answer', 'exams.*']);

        $correctAnswers = 0;
        $answered = count($results);
        $totalQuestions = $this->getQuestionCount();
        foreach ($results as $result) {
            if ($result->selected_answer == $result->correct_answer)
                $correctAnswers++;
            echo "<br>";
        }
        $percentageScore = ($correctAnswers / $totalQuestions) * 100;
        $wrongAnswers = $totalQuestions - $correctAnswers;
        $unansweredQuestions = $totalQuestions - $answered;
        // return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
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
        return  $questionCount = $this->getExamSettings()['question_count'] ?? 10;
        // return min(max($questionCount, 1), 50); // limit between 1 and 50
    }

    public function getTotalAnswered($candidate)
    {
        return Exam::where('candidate_id', $candidate->id)->count();
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
