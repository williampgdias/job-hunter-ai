<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Hunter AI Test</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="bg-gray-100 p-10">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Job Hunter AI 🤖</h1>

        <label class="block font-bold mb-2">Job Description (Cole a vaga aqui):</label>
        <textarea id="job" class="w-full border p-2 rounded h-32" placeholder="Paste job description..."></textarea>

        <label class="block font-bold mt-4 mb-2">My Resume (Cole seu CV aqui):</label>
        <textarea id="resume" class="w-full border p-2 rounded h-32" placeholder="Paste your resume text..."></textarea>

        <button onclick="generate()" class="bg-blue-600 text-white px-4 py-2 mt-4 rounded hover:bg-blue-700 w-full">
            Generate Cover Letter
        </button>

        <div class="mt-8">
            <h3 class="font-bold text-gray-700 mb-2">Recent History 🕦</h3>
            <div id="history-list" class="space-y-2">
                <p class="text-sm text-gray-400">Loading history...</p>
            </div>
        </div>

        <div id="result" class="mt-6 p-4 bg-gray-50 border rounded hidden">
            <h3 class="font-bold">Result:</h3>
            <div id="output" class="prose prose-slate max-w-none text-gray-700 mt-2 p-6 bg-white rounded border"></div>
        </div>
    </div>

    <!-- JS -->
    <script>
    // Function to Load History
    async function loadHistory() {
        const listDiv = document.getElementById('history-list');
        try {
            const response = await fetch('/api/history');
            const jobs = await response.json();

            listDiv.innerHTML = "";

            jobs.forEach(job => {
                // Create a clickable card for each job
                const item = document.createElement('div');
                item.className = "p-3 bg-white border rounded hover:bg-gray-50 cursor-pointer transition";

                // Formate date nicely
                const date = new Date(job.created_at).toLocaleDateString('en-IE', {
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const publicUrl = `/letter/${job.uuid}`;

                item.innerHTML = `
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-blue-600 truncate max-w-[200px] block" title="${job.title}">${job.title ? job.title : 'Job #' + job.id}</span>
                        <div class="flex items-center gap-2">
                            <a href="${publicUrl}" target="_blank" onclick="event.stopPropagation()" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200" title="Open Public Link">
                                🔗 Link
                            </a>
                            <span class="text-xs text-gray-400">${date}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 truncate mt-1">${job.description.substring(0, 50)}...</p>
                `;

                // Click event: Show this cover letter in the main box
                item.onclick = () => {
                    const output = document.getElementById('output');
                    const resultDiv = document.getElementById('result');

                    // Show hidden box
                    resultDiv.classList.remove('hidden');

                    // Render Markdown
                    output.innerHTML = marked.parse(job.generated_cover_letter);

                    // Scroll to result
                    resultDiv.scrollIntoView({
                        behavior: 'smooth'
                    });
                };

                listDiv.appendChild(item);
            });
        } catch (error) {
            console.error("Error loading history:", error);
        }
    }

    // Load history immediately when page open
    loadHistory();

    // Generate Function
    async function generate() {
        const btn = document.querySelector('button');
        const output = document.getElementById('output');
        const resultDiv = document.getElementById('result');

        btn.innerText = "Thinking... (Please wait)";
        btn.disabled = true;

        const data = {
            job_description: document.getElementById('job').value,
            my_resume: document.getElementById('resume').value
        };

        try {
            const response = await fetch('/api/generate-cover-letter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const json = await response.json();
            resultDiv.classList.remove('hidden');

            if (json.data) {
                output.innerHTML = marked.parse(json.data.cover_letter);
                loadHistory();
            } else {
                output.innerText = "Error: " + JSON.stringify(json);
            }
        } catch (error) {
            alert("Error connecting to server!");
            console.error(error);
        }

        btn.innerText = "Generate Cover Letter";
        btn.disabled = false;
    }
    </script>
</body>

</html>
