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

            // Loading State (Premium Glassmorphism)
            resDiv.innerHTML = `
                <div class="animate-fade-up flex flex-col items-center justify-center p-12 bg-white/30 backdrop-blur-xl rounded-[2.5rem] border border-white/40 shadow-2xl max-w-lg mx-auto mt-10">
                    <div class="relative w-16 h-16 mb-6">
                        <div class="absolute inset-0 border-4 border-blue-500/30 rounded-full animate-ping"></div>
                        <div class="absolute inset-0 border-4 border-t-blue-600 rounded-full animate-spin"></div>
                    </div>
                    <p class="text-xl font-bold text-white tracking-[0.2em] uppercase animate-pulse drop-shadow-md">Processing...</p>
                    <p class="text-white/80 text-sm mt-2 font-medium">Please wait while we fetch your media</p>
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
                    <div class="animate-fade-up p-8 bg-red-500/95 backdrop-blur-xl text-white rounded-[2rem] font-bold flex flex-col items-center gap-4 border border-red-400/50 shadow-[0_20px_50px_rgba(239,68,68,0.3)] max-w-md mx-auto mt-10">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-2">
                            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-2xl font-black">Oops! Failed</h3>
                        <span class="text-base text-center font-medium opacity-90 leading-relaxed max-w-xs">${err.message || "An unexpected error occurred"}</span>
                        <button onclick="document.getElementById('result').innerHTML=''" class="mt-4 px-6 py-2 bg-white/20 hover:bg-white/30 rounded-full text-sm transition-colors cursor-pointer">Dismiss</button>
                    </div>`;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }
});

function renderSingle(data, container) {
    const dl = `download.php?action=download&url=${encodeURIComponent(data.url)}`;

    // Balanced height: 55vh (slightly more than half) - User requested "a little bigger"
    const mediaHtml = data.type === 'video'
        ? `<video controls class="w-auto h-full max-h-[55vh] mx-auto rounded-[1.2rem] shadow-inner bg-black"><source src="${dl}"></video>`
        : `<img src="${dl}" class="w-auto h-full max-h-[55vh] mx-auto rounded-[1.2rem] shadow-inner">`;

    const icon = data.type === 'video' ? 'video' : 'image';
    const typeLabel = data.type === 'video' ? 'Video' : 'Photo';

    // Premium Single Card Design (Balanced)
    container.innerHTML = `
        <div class="animate-fade-up max-w-sm mx-auto bg-white/60 backdrop-blur-2xl p-6 rounded-[2rem] border border-white/60 shadow-[0_30px_60px_rgba(0,0,0,0.12)] flex flex-col items-center gap-5 mt-6 relative overflow-hidden group">
            
            <!-- Atmospheric Glow -->
            <div class="absolute -top-20 -left-20 w-60 h-60 bg-purple-500/20 rounded-full blur-[80px] pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-blue-500/20 rounded-full blur-[80px] pointer-events-none"></div>

            <div class="w-full relative z-10 transition-transform duration-500 hover:scale-[1.02] flex justify-center">
                <div class="bg-white p-2 rounded-[1.5rem] shadow-xl border border-white/50 inline-block">
                    <div class="relative overflow-hidden rounded-[1.2rem]">
                        ${mediaHtml}
                        <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-md text-white px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1 border border-white/20">
                            <i data-lucide="${icon}" class="w-3 h-3"></i> ${typeLabel}
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="${dl}" class="relative z-10 w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white text-center py-4 rounded-xl font-black text-lg shadow-[0_10px_30px_rgba(79,70,229,0.4)] hover:shadow-[0_20px_40px_rgba(79,70,229,0.6)] transition-all transform hover:-translate-y-1 active:translate-y-0.5 flex items-center justify-center gap-2 group">
                <span class="bg-white/20 p-1.5 rounded-full group-hover:rotate-12 transition-transform">
                    <i data-lucide="download" class="w-5 h-5"></i>
                </span>
                <span>Download</span>
            </a>
            
            <p class="text-slate-500 text-xs font-medium relative z-10">Safe & Secure Download by MySeoFan</p>
        </div>`;

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function renderMultiple(mediaList, container) {
    let gridHtml = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full px-4 md:px-0">`;

    mediaList.forEach((item, index) => {
        const dl = `download.php?action=download&url=${encodeURIComponent(item.url)}`;
        const mediaHtml = item.type === 'video'
            ? `<video controls class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"><source src="${dl}"></video>`
            : `<img src="${dl}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">`;

        const icon = item.type === 'video' ? 'video' : 'image';

        gridHtml += `
            <div class="group relative bg-white rounded-[2rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-slate-100 flex flex-col">
                <!-- Media Area -->
                <div class="relative w-full aspect-[4/5] bg-slate-100 overflow-hidden isolate">
                    <!-- Loading Skeleton Background -->
                    <div class="absolute inset-0 bg-slate-200 animate-pulse -z-10"></div>
                    
                    ${mediaHtml}
                    
                    <!-- Index Badge -->
                    <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-md text-slate-800 text-xs font-black px-3 py-1.5 rounded-xl shadow-lg border border-white/50 z-20">
                        #${String(index + 1).padStart(2, '0')}
                    </div>
                    
                    <!-- Type Badge -->
                    <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-md text-white p-2.5 rounded-full z-20 border border-white/10">
                        <i data-lucide="${icon}" class="w-4 h-4"></i>
                    </div>

                    <!-- Overlay Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>
                </div>

                <!-- Action Area -->
                <div class="p-5 relative z-20 bg-white">
                    <a href="${dl}" class="w-full bg-slate-900 hover:bg-blue-600 text-white py-4 rounded-xl font-bold text-sm shadow-lg transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02] hover:shadow-blue-500/30">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Download ${item.type === 'video' ? 'Video' : 'Photo'}</span>
                    </a>
                </div>
            </div>
        `;
    });

    gridHtml += `</div>`;

    // Multiple Header
    container.innerHTML = `
        <div class="flex flex-col gap-10 items-center animate-fade-up w-full max-w-6xl mx-auto mt-8">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-lg px-6 py-2 rounded-full border border-white/30 shadow-lg mb-2">
                    <i data-lucide="layers" class="w-5 h-5 text-white"></i>
                    <span class="text-white font-bold tracking-wide">ALBUM FOUND</span>
                </div>
                <h3 class="text-3xl md:text-5xl font-black text-white drop-shadow-xl tracking-tight">
                    ${mediaList.length} <span class="text-white/80 font-bold text-2xl md:text-4xl">Media Items Ready</span>
                </h3>
            </div>
            ${gridHtml}
            
            <div class="mt-8 p-6 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 text-center max-w-2xl">
                 <p class="text-white/90 text-sm font-medium">✨ Pro Tip: You can download items individually by clicking the buttons above.</p>
            </div>
        </div>`;

    if (typeof lucide !== 'undefined') lucide.createIcons();
}
