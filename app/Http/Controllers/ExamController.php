<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Exam;
use App\Models\ExamSetting;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
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
        $timeRemain     = $this->getTimeRemaining($candidate->id);
        $examSettings = $this->getExamSettings();


        // return view('result', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));

        // $question = Question::inRandomOrder()->take($this->getQuestionCount())->first();

        // DISPLAY QUESTION
        $question = Question::whereNotIn('id', function ($query) use ($candidate) {
            $query->select('question_id')->from('exams')->where('candidate_id', $candidate->id);
        })->first();
        // ->inRandomOrder()


        if ($questionNumber == $totalQuestions || $timeRemain < 3 || empty($question)) {

            // $results = Exam::join('questions', 'exams.question_id', '=', 'questions.id')
            //     ->where('exams.candidate_id', $candidate->id)
            //     ->get(['questions.correct_answer', 'exams.*']);
            // leftjoin('exams', 'exams.question_id', '=', 'questions.id')

            // foreach ($results as $result) {
            //     if ($result->selected_answer != null)
            //         echo $result->question;
            // }

            $results = Question::leftjoin('exams', function (JoinClause $join) use ($candidate) {
                $join->on('exams.question_id', '=', 'questions.id')->where('exams.candidate_id', $candidate->id);
            })->get(['exams.selected_answer', 'questions.*']);

            // dd($results->whereNotNull('selected_answer')->count());

            $correctAnswers = $score = 0;
            $answered = $results->whereNotNull('selected_answer')->count();

            foreach ($results as $result) {
                if ($result->selected_answer == $result->correct_answer) {
                    $correctAnswers++;
                    $score++;
                }
                elseif($result->selected_answer == "") {
                    $score += 0;
                } else {
                    $score += (1 - $examSettings['negative']);
                }
            }
            $percentageScore = ($score / $totalQuestions) * 100;
            $unansweredQuestions = $totalQuestions - $answered;
            $wrongAnswers = $totalQuestions - $correctAnswers - $unansweredQuestions;
            return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
        }





        $endTime = $timeRemain ?? ($examSettings['time_limit'] ?? 10) * 60;

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    }

    public function previous($candidate, $question)
    {
        $candidate = Candidate::find($candidate);
        $question  = Question::find($question);

        $selected_answer = Exam::where('question_id', $question->id)->first()->selected_answer;

        if (empty($candidate) || empty($question))
            die("This question or candidate may have deleted.");

        $questionNumber = $this->getTotalAnswered($candidate);
        $totalQuestions = $this->getQuestionCount();
        $timeRemain     = $this->getTimeRemaining($candidate->id);

        $examSettings = $this->getExamSettings();



        $endTime = $timeRemain ?? ($examSettings['time_limit'] ?? 10) * 60;

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions', 'selected_answer'));
    }


    // SAVE ANSWER
    public function answer(Request $request)
    {
        $candidate = Candidate::find($request->candidate_id);
        $answer = $request->input('answer');

        $examSettings   = $this->getExamSettings();
        $totalQuestions = $this->getQuestionCount();
        $timeRemain     = $this->getTimeRemaining($candidate->id);
        $questionNumber = $this->getTotalAnswered($candidate) + 1;

        if ($this->getQuestionCount() > $this->getTotalAnswered($candidate)) {

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
        } else {
            $candidate_answer = Exam::where('candidate_id', $request->candidate_id)
                ->where('question_id', $request->question_id)
                ->update(

                    [
                        'candidate_id' => $request->candidate_id,
                        'question_id' => $request->question_id,
                        'selected_answer' => $request->answer,
                        'is_correct' => 1 //$request->is_correct,
                    ]
                );
        }

        $question = Question::whereNotIn('id', function ($query) use ($candidate) {
            $query->select('question_id')->from('exams')->where('candidate_id', $candidate->id);
        })->first(); //->inRandomOrder()



        if ($questionNumber == $totalQuestions || $timeRemain < 3 || empty($question)) {

            // $results = Exam::join('questions', 'exams.question_id', '=', 'questions.id')
            //     ->where('exams.candidate_id', $candidate->id)
            //     ->get(['questions.correct_answer', 'exams.*']);
            // leftjoin('exams', 'exams.question_id', '=', 'questions.id')

            // foreach ($results as $result) {
            //     if ($result->selected_answer != null)
            //         echo $result->question;
            // }

            $results = Question::leftjoin('exams', function (JoinClause $join) use ($candidate) {
                $join->on('exams.question_id', '=', 'questions.id')->where('exams.candidate_id', $candidate->id);
            })->get(['exams.selected_answer', 'questions.*']);

            // dd($results->whereNotNull('selected_answer')->count());

            $correctAnswers = $score = 0;
            $answered = $results->whereNotNull('selected_answer')->count();

            foreach ($results as $result) {
                if ($result->selected_answer == $result->correct_answer) {
                    $correctAnswers++;
                    $score++;
                }
                elseif($result->selected_answer == "") {
                    $score += 0;
                } else {
                    $score += (1 - $examSettings['negative']);
                }
            }
            $percentageScore = ($score / $totalQuestions) * 100;
            $unansweredQuestions = $totalQuestions - $answered;
            $wrongAnswers = $totalQuestions - $correctAnswers - $unansweredQuestions;
            return view('result', compact('candidate', 'answered', 'percentageScore', 'totalQuestions', 'correctAnswers', 'wrongAnswers', 'unansweredQuestions'));
        }

        // return view('result', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));


        $endTime = $timeRemain ?? ($examSettings['time_limit'] ?? 10) * 60;

        return view('test', compact('candidate', 'question', 'endTime', 'questionNumber', 'totalQuestions'));
    }

    private function getQuestionCount()
    {
        return  $this->getExamSettings()['question_count'] ?? 10;
    }

    public function getTotalAnswered($candidate)
    {
        return Exam::where('candidate_id', $candidate->id)->count();
    }

    private function getExamSettings()
    {
        $examSettings = [];

        foreach (ExamSetting::all() as $setting) {
            $examSettings[$setting->name] = $setting->value;
        }

        return $examSettings;
    }
    private function getTimeRemaining($candidate)
    {
        $examSettings = $this->getExamSettings();
        return Exam::where('candidate_id', $candidate)->first()->time_remain ?? ($examSettings['time_limit'] ?? 10) * 60;
    }
    public function updateTimeRemaining(Request $request)
    {
        $remainingTime = $request->input('remaining_time');
        $candidate = $request->input('candidate');

        // Update the time remaining value in the database
        Exam::where('candidate_id', $candidate)
            ->update(['time_remain' => $remainingTime]);

        return response()->json(['message' => 'Time remaining updated successfully.']);
    }
}
