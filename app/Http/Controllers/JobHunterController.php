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
            'job_description' => 'required|string',
            'my_resume' => 'required|string'
        ]);

        $jobDescription = $request->input('job_description');
        $myResume = $request->input('my_resume');
        $apiKey = env('GEMINI_API_KEY');

        // Construct the Prompt for Gemini
        $systemInstruction = "You are an expert Career Coach.
        Analyze the Job Description and the User's Resume.

        Return ONLY a raw JSON object (no markdown formatting, no code blocks) with this exact structure:
        {
            \"job_title\": \"Extract the job title from description\",
            \"company_name\": \"Extract company name (or 'Unknown')\",
            \"cover_letter\": \"The full professional cover letter text (use \\n for line breaks)\"
        }

        Cover Letter Rules:
        - Be professional, enthusiastic, and concise.
        - Highlight matching skills from the resume.
        - Use placeholders like [Date] if needed, but try to fill specific details.";

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
                    'message' => 'API Error',
                    'details' => $response->json()
                ], $response->status());
            }

            $result = $response->json();

            // 5. Extração da resposta
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = $result['candidates'][0]['content']['parts'][0]['text'];

                // Limpeza: Às vezes o Gemini manda ```json ... ```. Vamos limpar isso.
                $cleanJson = str_replace(['```json', '```'], '', $rawText);
                $data = json_decode($cleanJson, true);

                // Fallback: Se o JSON falhar, usa o texto puro como carta e um título genérico
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $coverLetter = $rawText;
                    $title = 'Job Opportunity (Review Title)';
                } else {
                    $coverLetter = $data['cover_letter'] ?? $rawText;
                    $title = ($data['job_title'] ?? 'Job') . ' @ ' . ($data['company_name'] ?? 'Company');
                }

                // Salvar no Banco com o Título certo!
                $job = JobOpportunity::create([
                    'title' => substr($title, 0, 250), // Limita tamanho pra não dar erro
                    'description' => $jobDescription,
                    'generated_cover_letter' => $coverLetter,
                    'status' => 'generated'
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'data' => [
                        'job_id' => $job->id,
                        'cover_letter' => $coverLetter
                    ]
                ]);
            } else {
                return response()->json(['error' => 'Formato inesperado', 'raw' => $result], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch the recent history of generated applications.
     */
    public function getHistory()
    {
        // Get the last 5 jobs, ordered by newest first
        $history = JobOpportunity::latest()->take(5)->get();

        return response()->json($history);
    }

    /**
     * Public view for recruiters to see the cover letter.
     */
    public function showPublic($uuid)
    {
        // Find by UUID or show 404 error if not found
        $job = JobOpportunity::where('uuid', $uuid)->firstOrFail();

        return view('public-letter', ['job' => $job]);
    }
}