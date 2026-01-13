document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Paste functionality
    const btnPaste = document.getElementById('btnPaste');
    if (btnPaste) {
        btnPaste.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                const instaUrl = document.getElementById('instaUrl');
                if (instaUrl) instaUrl.value = text;
            } catch (err) {
                console.error('Failed to read clipboard', err);
                alert('Clipboard access denied. Please paste manually.');
            }
        });
    }

    // Form Submission
    const downloadForm = document.getElementById('downloadForm');
    if (downloadForm) {
        downloadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('instaUrl');
            const resDiv = document.getElementById('result');
            const url = input.value.trim();
            if (!url) return;

            // Loading State (Premium Design)
            resDiv.innerHTML = `
                <div class='flex flex-col items-center gap-6 py-10 bg-white/20 backdrop-blur-md rounded-3xl p-8 border border-white/30 shadow-xl'>
                    <div class='spinner'></div>
                    <p class='font-bold text-white uppercase tracking-widest text-sm animate-pulse'>Fetching Media...</p>
                </div>`;

            try {
                const res = await fetch('download.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url })
                });
                const data = await res.json();
                resDiv.innerHTML = '';

                if (data.status === 'single') {
                    renderSingle(data, resDiv);
                } else if (data.status === 'multiple') {
                    renderMultiple(data.media, resDiv);
                } else {
                    throw new Error(data.error || 'Content not found or private');
                }
            } catch (err) {
                console.error(err);
                resDiv.innerHTML = `
                    <div class='p-8 bg-red-500/90 backdrop-blur-md text-white rounded-3xl font-bold flex flex-col items-center gap-4 border border-red-400 shadow-xl fade-in'>
                        <i data-lucide="alert-circle" class="w-10 h-10"></i>
                        <span class="text-lg text-center">${err.message || "An unexpected error occurred"}</span>
                    </div>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }
});

function renderSingle(data, container) {
    const dl = `download.php?action=download&url=${encodeURIComponent(data.url)}`;

    const mediaHtml = data.type === 'video'
        ? `<video controls class="w-full h-auto rounded-[2rem]"><source src="${dl}"></video>`
        : `<img src="${dl}" class="w-full h-auto rounded-[2rem]">`;

    container.innerHTML = `
        <div class="flex flex-col gap-8 items-center fade-in bg-white/10 backdrop-blur-lg p-8 rounded-[3rem] border border-white/20 shadow-2xl">
            <div class="relative group w-full max-w-md overflow-hidden shadow-2xl border-4 border-white/50 rounded-[2.5rem]">
                ${mediaHtml}
            </div>
            <a href="${dl}" class="w-full max-w-xs bg-blue-600 text-white text-center py-4 rounded-2xl font-black text-xl shadow-lg hover:bg-blue-700 transition-all flex items-center justify-center gap-2 transform hover:scale-105 active:scale-95">
                <i data-lucide="download" class="w-6 h-6"></i> Download
            </a>
        </div>`;

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderMultiple(mediaList, container) {
    let gridHtml = `<div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">`;

    mediaList.forEach((item, index) => {
        const dl = `download.php?action=download&url=${encodeURIComponent(item.url)}`;
        const mediaHtml = item.type === 'video'
            ? `<video controls class="w-full h-64 object-cover rounded-2xl"><source src="${dl}"></video>`
            : `<img src="${dl}" class="w-full h-64 object-cover rounded-2xl">`;

        gridHtml += `
            <div class="bg-white/80 backdrop-blur p-4 rounded-3xl shadow-lg border border-white/50 flex flex-col gap-4">
                <div class="relative w-full rounded-2xl overflow-hidden shadow-inner bg-black/5">
                    <span class="absolute top-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm z-10">
                        ${index + 1}
                    </span>
                    ${mediaHtml}
                </div>
                <a href="${dl}" class="w-full bg-blue-600 text-white text-center py-3 rounded-xl font-bold text-sm shadow hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i> Download ${item.type === 'video' ? 'Video' : 'Photo'}
                </a>
            </div>
        `;
    });

    gridHtml += `</div>`;

    container.innerHTML = `
        <div class="flex flex-col gap-6 items-center fade-in w-full">
            <h3 class="text-white text-xl font-bold drop-shadow-md">Found ${mediaList.length} items</h3>
            ${gridHtml}
        </div>`;

    if (typeof lucide !== 'undefined') lucide.createIcons();
}
