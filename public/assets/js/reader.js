// Pembaca PDF di dalam halaman: halaman digambar ke <canvas> lewat pdf.js,
// sehingga tidak ada tombol unduh/cetak bawaan penampil PDF browser.
import { GlobalWorkerOptions, getDocument } from '../pdfjs/pdf.min.js';

GlobalWorkerOptions.workerSrc = new URL(
    '../pdfjs/pdf.worker.min.js',
    import.meta.url
).href;

const view = document.getElementById('pdfViewer');

if (view) {
    const canvas = document.getElementById('pdfCanvas');
    const info = document.getElementById('pdfPage');
    const prev = document.getElementById('pdfPrev');
    const next = document.getElementById('pdfNext');
    const context = canvas.getContext('2d');
    const ratio = window.devicePixelRatio || 1;

    let pdf = null;
    let pageNumber = 1;
    let busy = false;

    async function draw() {
        if (!pdf || busy) {
            return;
        }

        busy = true;

        try {
            const page = await pdf.getPage(pageNumber);
            const base = page.getViewport({ scale: 1 });
            const scale = Math.min(view.clientWidth - 2, 900) / base.width;
            const viewport = page.getViewport({ scale: scale * ratio });

            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            canvas.style.width = Math.floor(viewport.width / ratio) + 'px';
            canvas.style.height = Math.floor(viewport.height / ratio) + 'px';

            await page.render({ canvasContext: context, viewport }).promise;

            info.textContent = 'Halaman ' + pageNumber + ' / ' + pdf.numPages;
            prev.disabled = pageNumber <= 1;
            next.disabled = pageNumber >= pdf.numPages;
        } catch (error) {
            info.textContent = 'Halaman gagal ditampilkan.';
        } finally {
            busy = false;
        }
    }

    prev.addEventListener('click', function () {
        if (pageNumber > 1) {
            pageNumber -= 1;
            draw();
        }
    });

    next.addEventListener('click', function () {
        if (pdf && pageNumber < pdf.numPages) {
            pageNumber += 1;
            draw();
        }
    });

    // Memblokir simpan/cetak halaman reader dan menu klik kanan pada halaman ebook.
    view.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && ['s', 'p'].includes(event.key.toLowerCase())) {
            event.preventDefault();
        }
    });

    let resizeTimer;

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(draw, 200);
    });

    getDocument({ url: view.dataset.src, withCredentials: true })
        .promise
        .then(function (document) {
            pdf = document;
            return draw();
        })
        .catch(function () {
            info.textContent = 'Ebook gagal dimuat.';
        });
}
