<!-- test.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>MCQ Exam <span class="float-right" id="timer"></span></h1>
        <hr>

        <strong>Question(s) answered {{ $questionNumber }} of {{ $totalQuestions }}</strong><br>
        {{-- <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                aria-valuenow="{{ ($questionNumber * $totalQuestions) / 100 }}" aria-valuemin="0" aria-valuemax="100"
                style="width: {{ ($questionNumber * $totalQuestions) / 100 }}%"></div>
        </div> --}}

        <form id="exam-form" method="post" action="{{ route('exam.answer') }}">
            @csrf

            <input type="hidden" id="question_id" name="question_id" value="{{ $question->id }}">
            <input type="hidden" id="candidate_id" name="candidate_id" value="{{ $candidate->id }}">

            <div class="form-group">
                <label for="question">{{ $question->question }}</label>
            </div>

            <div class="form-check">
                <input {{ $selected_answer && $selected_answer == 1 ? "checked='chedked'" : '' }} type="radio"
                    class="form-check-input" name="answer" value="1" required>
                <label class="form-check-label">{{ $question->option_1 }}</label>
            </div>

            <div class="form-check">
                <input {{ $selected_answer && $selected_answer == 2 ? "checked='chedked'" : '' }} type="radio"
                    class="form-check-input" name="answer" value="2" required>
                <label class="form-check-label">{{ $question->option_2 }}</label>
            </div>

            <div class="form-check">
                <input {{ $selected_answer && $selected_answer == 3 ? "checked='chedked'" : '' }} type="radio"
                    class="form-check-input" name="answer" value="3" required>
                <label class="form-check-label">{{ $question->option_3 }}</label>
            </div>

            <div class="form-check">
                <input {{ $selected_answer && $selected_answer == 4 ? "checked='chedked'" : '' }} type="radio"
                    class="form-check-input" name="answer" value="4" required>
                <label class="form-check-label">{{ $question->option_4 }}</label>
            </div>

            @if ($question->id > 1)
            <a type="button" id="previous" class="btn btn-primary" href="/exam/{{ $candidate->id}}/{{ $question->id - 1}}">Previous</a>
            @endif

            <button type="submit" class="btn btn-primary">Next</button>
            <button type="button" id="finish-test" class="btn btn-warning">Finish Test</button>

        </form>





    </div>


@endsection

@push('scripts')
    <script>
        var duration = {{ $endTime }};

        // Start count-down timer
        var timer = setInterval(function() {
            var minutes = Math.floor(duration / 60);
            var seconds = duration % 60;

            var timeString = ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2);

            document.getElementById("timer").innerHTML = timeString;

            duration--;

            if (duration < 0) {
                clearInterval(timer);
                document.getElementById("exam-form").submit();
            }
            update_time(duration);
        }, 1000);


        function update_time(timeString) {
            $.ajax({
                url: '{{ route("exam.updateTimeRemaining") }}',
                type: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {
                    remaining_time: timeString,
                    candidate: document.getElementById("candidate_id").value
                },
                success: function(response) {
                    console.log('Time remaining updated successfully.', response);
                },
                error: function(error) {
                    console.log('Error updating time remaining:', error);
                }
            });
        }
        $("#previous").click(function() {
            previous();
        });
        $("#finish-test").click(function() {
            if(confirm("Are you sure you want to finish the test? Note that you may not take this test again If any questions are remaing to answer.")) {
                clearInterval(timer);
                duration = 0;
                update_time(-1);
                document.getElementById("exam-form").submit();
            }

        });
    </script>
@endpush
