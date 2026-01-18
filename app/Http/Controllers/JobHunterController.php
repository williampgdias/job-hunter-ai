<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gemini\Laravel\Facades\Gemini;
use App\Models\JobOpportunity;

class JobHunterController extends Controller
{
    /**
     * Receives a job description and generates a cover letter using Gemini AI.
     */
    public function analyzeJob(Request $request)
    {
        // validate the input
        $request->validate([
            'job_description' => 'required|string|min:50',
            'my_resume' => 'required|string|min:50'
        ]);

        $jobDescription = $request->input('job_description');
        $myResume = $request->input('my_resume');

        // Construct the Prompt for Gemini
        $prompt = "
            Role: You are an expert Career Coach and Copywriter.
            Task: Write a professional, persuasive cover letter.

            Context:
            - Candidate Resume: {$myResume}
            - Job Description: {$jobDescription}

            Instructions:
            1. Analyze the job description to identify key requirements.
            2. Match the candidate's skills from the resume to those requirements.
            3. Write a cover letter in English that highlights why the candidate is the perfect fit.
            4. Keep the tone professional, enthusiastic, but concise.
            5. Do NOT invent skills the candidate does not have.
        ";

        // Call Gemini API
        $result = Gemini::geminiPro()->generateContent($prompt);
        $coverLetter = $result->text();

        // Save to Database (History)
        $job = JobOpportunity::create([
            'title' => 'Pending Title',
            'description' => $jobDescription,
            'generated_cover_letter' => $coverLetter,
            'status' => 'generated'
        ]);

        // Return the result
        return response()->json([
            'message' => 'Cover letter generated successfully!',
            'data' => [
                'job_id' => $job->id,
                'cover_letter' => $coverLetter
            ]
        ]);
    }
}