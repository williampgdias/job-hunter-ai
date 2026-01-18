<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application for {{ $job->title ?? 'Web Developer' }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
    .editable-area:hover {
        outline: 2px dashed #bfdbfe;
        cursor: text;
    }

    .editable-area:focus {
        outline: 2px solid #2563eb;
        background-color: #feffff;
    }

    .prose p {
        margin-top: 0.25em !important;
        margin-bottom: 0.25em !important;
        line-height: 1.5 !important;
    }

    .prose h1,
    .prose h2,
    .prose h3 {
        margin-top: 0.5em !important;
        margin-bottom: 0.3em !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .print-clean {
            outline: none !important;
            box-shadow: none !important;
            border: none !important;
            background-color: white !important;
        }

        @page {
            margin: 2cm;
        }

        body {
            background: white;
        }
    }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col font-sans">

    <textarea id="raw-content" class="hidden">{{ $job->generated_cover_letter }}</textarea>

    <div class="no-print bg-white border-b py-3 px-6 flex justify-between items-center sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-gray-500">Preview Mode</span>
            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Click text to edit</span>
        </div>
        <button onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center gap-2 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Save as PDF
        </button>
    </div>

    <main class="grow max-w-3xl mx-auto w-full p-6 md:p-12 print-clean">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100 print-clean">
            <div class="h-2 bg-blue-600 w-full"></div>

            <div class="p-8 md:p-12 print-clean">

                <div id="loading" class="text-center text-gray-400 py-10 no-print">
                    <p class="animate-pulse">✨ Formatting document...</p>
                </div>

                <div id="content" contenteditable="true"
                    class="hidden prose prose-slate max-w-none text-gray-800 leading-relaxed font-serif editable-area print-clean outline-none p-2 rounded">
                </div>

                <div id="error-box" class="hidden bg-red-50 text-red-600 p-4 rounded border border-red-200 no-print">
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t mt-12 py-8 no-print">
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
            const rawElement = document.getElementById('raw-content');
            const rawText = rawElement ? rawElement.value : '';
            const contentDiv = document.getElementById('content');
            const loadingDiv = document.getElementById('loading');

            if (!rawText.trim()) throw new Error("Empty content.");
            if (typeof marked === 'undefined') throw new Error("Marked.js missing.");

            contentDiv.innerHTML = marked.parse(rawText);

            loadingDiv.classList.add('hidden');
            contentDiv.classList.remove('hidden');

        } catch (error) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error-box').classList.remove('hidden');
            document.getElementById('error-box').innerText = "Error: " + error.message;
        }
    });
    </script>
</body>

</html>
