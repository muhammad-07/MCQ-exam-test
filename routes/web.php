<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/exam', [ExamController::class, 'index']);
Route::post('/exam/start/', [ExamController::class, 'start'])->name('start.exam');
Route::get('/exam/question/{id}', [ExamController::class, 'question'])->name('exam.question');
Route::post('/exam/answer', [ExamController::class, 'answer'])->name('exam.answer');
Route::get('/exam/result', [ExamController::class, 'result'])->name('exam.result');
Route::get('/exam/finish', [ExamController::class, 'finish'])->name('exam.finish');
Route::get('/exam/reopen', [ExamController::class, 'reopen'])->name('exam.reopen');
