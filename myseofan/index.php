<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instagram Downloader</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

<!-- ================= HEADER ================= -->
<header class="bg-white shadow-md">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between py-6 px-4">
        <h1 id="titleText" class="text-2xl font-bold text-green-600">
            Instagram Downloader
        </h1>

        <nav class="flex gap-4 items-center text-gray-600 font-medium mt-3 md:mt-0">
            <a href="#" class="nav-link hover:text-green-600" data-page="home" id="navHome">Home</a>
            <a href="#" class="nav-link hover:text-green-600" data-page="how" id="navHow">How to Use</a>
            <a href="#" class="nav-link hover:text-green-600" data-page="about" id="navAbout">About</a>

            <select id="langSelector"
                class="ml-4 border border-gray-300 rounded-lg px-2 py-1">
                <option value="en">English</option>
                <option value="id">Indonesia</option>
            </select>
        </nav>
    </div>
</header>

<!-- ================= MAIN ================= -->
<main class="container mx-auto flex-1 px-4 py-8">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">

        <!-- HOME -->
        <section id="page-home" class="page">
            <p id="subtitle" class="text-gray-600 text-center mb-6">
                Paste your Instagram link and download media instantly.
            </p>

            <form id="downloadForm" class="flex flex-col md:flex-row gap-4">
                <div class="flex flex-1">
                    <input
                        type="text"
                        id="instaUrl"
                        placeholder="Paste Instagram link..."
                        required
                        class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:ring-2 focus:ring-green-500"
                    >
                    <button
                        type="button"
                        id="pasteBtn"
                        class="border border-l-0 border-gray-300 px-4 rounded-r-lg bg-gray-100 hover:bg-gray-200">
                        Paste
                    </button>
                </div>

                <button
                    type="submit"
                    id="downloadBtn"
                    class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-500">
                    Download
                </button>
            </form>

            <div id="result" class="mt-8"></div>
        </section>

        <!-- HOW -->
        <section id="page-how" class="page hidden">
            <h2 id="howTitle" class="text-xl font-bold mb-4">How to Use</h2>
            <ol id="howList" class="list-decimal list-inside space-y-2 text-gray-600">
                <li>Copy Instagram post / reel / carousel link</li>
                <li>Paste link into input field</li>
                <li>Click Download</li>
                <li>Preview and download media</li>
            </ol>
        </section>

        <!-- ABOUT -->
        <section id="page-about" class="page hidden">
            <h2 id="aboutTitle" class="text-xl font-bold mb-4">About</h2>
            <p id="aboutText" class="text-gray-600 mb-2">
                Simple Instagram Downloader powered by Cobalt API.
            </p>
            <p class="text-sm text-gray-500">
                Only public Instagram content is supported.
            </p>
        </section>

    </div>
</main>

<!-- ================= FOOTER ================= -->
<footer class="bg-white shadow-inner mt-8">
    <div class="container mx-auto py-4 text-center text-gray-500">
        © 2026 Instagram Downloader
    </div>
</footer>

<!-- ================= SCRIPT ================= -->
<script>
/* ---------- NAV ---------- */
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
        document.getElementById('page-' + link.dataset.page).classList.remove('hidden');
    });
});

/* ---------- TRANSLATION ---------- */
const i18n = {
    en: {
        title: "Instagram Downloader",
        home: "Home",
        how: "How to Use",
        about: "About",
        subtitle: "Paste your Instagram link and download media instantly.",
        download: "Download",
        paste: "Paste",
        howSteps: [
            "Copy Instagram post / reel / carousel link",
            "Paste link into input field",
            "Click Download",
            "Preview and download media"
        ],
        aboutText: "Simple Instagram Downloader powered by Cobalt API."
    },
    id: {
        title: "Pengunduh Instagram",
        home: "Beranda",
        how: "Cara Pakai",
        about: "Tentang",
        subtitle: "Tempel link Instagram dan unduh media secara instan.",
        download: "Unduh",
        paste: "Tempel",
        howSteps: [
            "Salin link postingan / reel / carousel Instagram",
            "Tempel link ke kolom input",
            "Klik Unduh",
            "Pratinjau dan unduh media"
        ],
        aboutText: "Aplikasi pengunduh Instagram sederhana berbasis Cobalt API."
    }
};

document.getElementById('langSelector').addEventListener('change', e => {
    const t = i18n[e.target.value];

    titleText.innerText = t.title;
    navHome.innerText = t.home;
    navHow.innerText = t.how;
    navAbout.innerText = t.about;
    subtitle.innerText = t.subtitle;
    downloadBtn.innerText = t.download;
    pasteBtn.innerText = t.paste;
    aboutText.innerText = t.aboutText;

    howList.innerHTML = t.howSteps.map(s => `<li>${s}</li>`).join('');
});

/* ---------- PASTE ---------- */
pasteBtn.addEventListener('click', async () => {
    try {
        instaUrl.value = await navigator.clipboard.readText();
    } catch {
        alert('Clipboard not supported. Paste manually.');
    }
});

/* ---------- DOWNLOAD ---------- */
downloadForm.addEventListener('submit', async e => {
    e.preventDefault();
    const url = instaUrl.value.trim();
    if (!url) return;

    result.innerHTML = `<p class="text-center text-gray-500">Loading...</p>`;

    const res = await fetch('download.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url })
    });

    const data = await res.json();
    if (data.status === 'single') renderSingle(data);
    if (data.status === 'multiple') renderCarousel(data.media);
});

/* ---------- RENDER ---------- */
function renderSingle(data) {
    const d = `download.php?action=download&url=${encodeURIComponent(data.url)}`;
    result.innerHTML = `
        <div class="flex flex-col gap-4 items-center">
            ${data.type === 'video'
                ? `<video controls class="rounded-lg max-w-full"><source src="${d}"></video>`
                : `<img src="${d}" class="rounded-lg max-w-full">`}
            <a href="${d}" class="bg-green-600 text-white px-6 py-2 rounded-lg">Download</a>
        </div>
    `;
}

function renderCarousel(media) {
    result.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            ${media.map((item,i)=>{
                const d = `download.php?action=download&url=${encodeURIComponent(item.url)}`;
                return `
                    <div class="border p-4 rounded-lg">
                        <div class="text-sm text-gray-500 mb-2">
                            Slide ${i+1} (${item.type})
                        </div>
                        ${item.type === 'video'
                            ? `<video controls class="w-full rounded"><source src="${d}"></video>`
                            : `<img src="${d}" class="w-full rounded">`}
                        <a href="${d}" class="block mt-3 bg-green-600 text-white text-center py-2 rounded">
                            Download
                        </a>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}
</script>

</body>
</html>
