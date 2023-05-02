<!-- test.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>MCQ Exam</h1>
        <hr>

        <strong>Question {{ $questionNumber }} of {{ $totalQuestions }}</strong><br>
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ ($questionNumber * $totalQuestions) / 100 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ ($questionNumber * $totalQuestions) / 100 }}%"></div>
          </div>

        <form method="post" action="{{ route('exam.answer') }}">
            @csrf

            <input type="hidden" name="question_id" value="{{ $question->id }}">
            <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">

            <div class="form-group">
                <label for="question">{{ $question->question }}</label>
            </div>

            <div class="form-check">
                <input type="radio" class="form-check-input" name="answer" value="1" required>
                <label class="form-check-label">{{ $question->option_1 }}</label>
            </div>

            <div class="form-check">
                <input type="radio" class="form-check-input" name="answer" value="2" required>
                <label class="form-check-label">{{ $question->option_2 }}</label>
            </div>

            <div class="form-check">
                <input type="radio" class="form-check-input" name="answer" value="3" required>
                <label class="form-check-label">{{ $question->option_3 }}</label>
            </div>

            <div class="form-check">
                <input type="radio" class="form-check-input" name="answer" value="4" required>
                <label class="form-check-label">{{ $question->option_4 }}</label>
            </div>

            <button type="submit" class="btn btn-primary">Next</button>
        </form>

        {{-- <a href="{{ $question->previousPageUrl() }}">Previous</a>
<a href="{{ $question->nextPageUrl() }}">Next</a> --}}


        <div id="timer">
            Time remaining: <span id="minutes">{{ $endTime }}</span>:<span id="seconds">00</span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var time = {{ 10 * 60 }};
        var timer = setInterval(function() {
            var minutes = Math.floor(time / 60);
            var seconds = time % 60;
            document.getElementById("minutes").innerHTML = minutes;
            document.getElementById("seconds").innerHTML = seconds < 10 ? "0" + seconds : seconds;
            time--;
            if (time < 0) {
                clearInterval(timer);
                document.getElementById("timer").innerHTML = "Time's up!";
                document.getElementById("test-form").submit();
            }
        }, 1000);
    </script>
@endpush
