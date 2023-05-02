<!-- exam.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>MCQ Exam</h1>
    <hr>

    <form method="post" action="{{ route('start.exam') }}">
        @csrf

        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Start Exam</button>
    </form>
</div>
@endsection
