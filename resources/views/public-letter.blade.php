<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application for {{ $job->title ?? 'Web Developer' }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col font-sans">

    <textarea id="raw-content" class="hidden">{{ $job->generated_cover_letter }}</textarea>

    <main class="flex-grow max-w-3xl mx-auto w-full p-6 md:p-12">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100">
            <div class="h-2 bg-blue-600 w-full"></div>

            <div class="p-8 md:p-12">

                <div id="loading" class="text-center text-gray-400 py-10">
                    <p class="animate-pulse">✨ Formatting document...</p>
                </div>

                <div id="content" class="hidden prose prose-slate max-w-none text-gray-800 leading-relaxed">
                </div>

                <div id="error-box" class="hidden bg-red-50 text-red-600 p-4 rounded border border-red-200">
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t mt-12 py-8">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-500 mb-2">
                🤖 Transparency Note: This cover letter was assisted by an
                <strong class="text-gray-800">AI Agent</strong> I built using
                <span class="text-blue-600 font-semibold">Laravel 11</span> and
                <span class="text-purple-600 font-semibold">Google Gemini</span>.
            </p>
            <p class="text-xs text-gray-400">
                <a href="#" class="underline hover:text-blue-600 ml-1">View Source Code on GitHub</a>
            </p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        try {
            // AGORA SIM: Pegamos o texto do HTML, sem misturar PHP no JS
            const rawElement = document.getElementById('raw-content');
            const rawText = rawElement ? rawElement.value : '';

            const contentDiv = document.getElementById('content');
            const loadingDiv = document.getElementById('loading');

            // Verificação de segurança
            if (!rawText.trim()) {
                throw new Error("The cover letter content seems to be empty.");
            }

            if (typeof marked === 'undefined') {
                throw new Error("Marked.js library failed to load.");
            }

            // Renderiza
            contentDiv.innerHTML = marked.parse(rawText);

            // Troca: Esconde loading, mostra conteúdo
            loadingDiv.classList.add('hidden');
            contentDiv.classList.remove('hidden');

        } catch (error) {
            document.getElementById('loading').classList.add('hidden');
            const errBox = document.getElementById('error-box');
            errBox.classList.remove('hidden');
            errBox.innerText = "Error: " + error.message;
            console.error(error);
        }
    });
    </script>
</body>

</html>
