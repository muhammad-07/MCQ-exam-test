@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
        
      <div class="card">
        <div class="card-header">Exam Result</div>

        <div class="card-body">
          <p>Dear {{ $candidate->name }},</p>
          <p>Your test has been submitted successfully.</p>

          <p>You have answered {{ $answered }} out of {{ $totalQuestions }} questions and your score is {{ $percentageScore }}% out of {{ $totalQuestions }}.</p>

          <p>Correct Answers: {{ $correctAnswers }}</p>

          <p>Wrong Answers: {{ $wrongAnswers }}</p>

          <p>Unanswered Questions: {{ $unansweredQuestions }}</p>

          <p>Thank you for taking the exam.</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
