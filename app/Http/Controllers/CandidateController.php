<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $candidate = Candidate::create($validatedData);

        return redirect()->route('exam.start', ['candidate' => $candidate->id]);
    }
}
