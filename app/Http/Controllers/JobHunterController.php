<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
        $apiKey = env('GEMINI_API_KEY');



        // Construct the Prompt for Gemini
        $systemInstruction = "
            Role: You are an expert Career Coach and Copywriter.
            Task: Write a professional, persuasive cover letter.

            Instructions:
            1. Analyze the job description provided by the user.
            2. Match the candidate's skills from the resume provided.
            3. Write a cover letter in English that highlights why the candidate is the perfect fit.
            4. Keep the tone professional, enthusiastic, but concise.
            5. Do NOT invent skills the candidate does not have.
        ";

        $userMessage = "
            Here is my resume:
            {$myResume}

            Here is the Job Description:
            {$jobDescription}

            Please write my cover letter now.
        ";

        // Prepare the Payload manually
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        $payload = [
            "system_instruction" => [
                "parts" => [["text" => $systemInstruction]]
            ],
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [[
                        "text" => "RESUME:\n{$myResume}\n\nJOB DESCRIPTION:\n{$jobDescription}"
                    ]]
                ]
            ]
        ];

        // Send Request using Laravel Http
        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            // Se a API retornar erro (ex: 400, 404, 500)
            if ($response->failed()) {
                return response()->json([
                    'message' => 'Erro na API do Google',
                    'details' => $response->json()
                ], $response->status());
            }

            $result = $response->json();

            // 5. Extração da resposta
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $coverLetter = $result['candidates'][0]['content']['parts'][0]['text'];

                // Salvar no Banco
                $job = JobOpportunity::create([
                    'title' => 'Job Application',
                    'description' => $jobDescription,
                    'generated_cover_letter' => $coverLetter,
                    'status' => 'generated'
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'data' => ['cover_letter' => $coverLetter]
                ]);
            } else {
                return response()->json(['error' => 'Formato inesperado', 'raw' => $result], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}