const form = document.getElementById('downloadForm');
const result = document.getElementById('result');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const url = document.getElementById('instaUrl').value.trim();
    if (!url) return;

    result.innerHTML = `<p>Loading...</p>`;

    try {
        const res = await fetch('download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url })
        });

        const data = await res.json();

        /* ===============================
           SINGLE MEDIA (VIDEO / PHOTO)
        =============================== */
        if (data.status === 'single') {
            renderSingleMedia(data);
            return;
        }

        /* ===============================
           MULTIPLE MEDIA (CAROUSEL)
        =============================== */
        if (data.status === 'multiple' && Array.isArray(data.media)) {
            renderMultipleMedia(data.media);
            return;
        }

        result.innerHTML = `<p style="color:red;">Failed to fetch media</p>`;

    } catch (err) {
        result.innerHTML = `<p style="color:red;">${err.message}</p>`;
    }
});

/* ======================================
   RENDER SINGLE MEDIA
====================================== */
function renderSingleMedia(data) {
    let html = `<div style="margin-top:15px;">`;

    if (data.type === 'video') {
        html += `
            <video controls style="max-width:100%;border-radius:10px;">
                <source src="${data.url}" type="video/mp4">
            </video>
        `;
    } else {
        html += `
            <img src="${data.url}" style="max-width:100%;border-radius:10px;" />
        `;
    }

    html += `
        <br><br>
        <a href="download.php?action=download&url=${encodeURIComponent(data.url)}"
           style="padding:10px 20px;background:#16a34a;color:#fff;border-radius:6px;text-decoration:none;">
           Download
        </a>
    </div>`;

    result.innerHTML = html;
}

/* ======================================
   RENDER MULTIPLE MEDIA (CAROUSEL)
====================================== */
function renderMultipleMedia(mediaList) {
    let html = `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;">`;

    mediaList.forEach((item, index) => {
        html += `
            <div style="border:1px solid #ddd;padding:10px;border-radius:10px;">
                ${
                    item.type === 'video'
                    ? `<video controls style="width:100%;border-radius:8px;">
                            <source src="${item.url}" type="video/mp4">
                       </video>`
                    : `<img src="${item.url}" style="width:100%;border-radius:8px;" />`
                }

                <br><br>
                <a href="download.php?action=download&url=${encodeURIComponent(item.url)}"
                   style="display:block;text-align:center;padding:8px;background:#16a34a;color:#fff;border-radius:6px;text-decoration:none;">
                   Download ${item.type} ${index + 1}
                </a>
            </div>
        `;
    });

    html += `</div>`;
    result.innerHTML = html;
}
