// Pembaca PDF di dalam halaman: halaman digambar ke <canvas> lewat pdf.js,
// sehingga tidak ada tombol unduh/cetak bawaan penampil PDF browser.
// pdf.js dimuat sebagai skrip klasik (UMD) agar tetap jalan di browser lama.
(function () {
    'use strict';

    var view = document.getElementById('pdfViewer');

    if (!view) {
        return;
    }

    var info = document.getElementById('pdfPage');
    var canvas = document.getElementById('pdfCanvas');
    var prev = document.getElementById('pdfPrev');
    var next = document.getElementById('pdfNext');
    var context = canvas.getContext('2d');
    var ratio = window.devicePixelRatio || 1;
    var pdfjsLib = window.pdfjsLib;

    if (!pdfjsLib || !context || typeof fetch !== 'function') {
        info.textContent = 'Pembaca ebook gagal dimuat di browser ini.';
        return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = view.dataset.worker;
    pdfjsLib.GlobalWorkerOptions.workerPort = null;

    var pdf = null;
    var pageNumber = 1;
    var busy = false;

    function draw() {
        if (!pdf || busy) {
            return Promise.resolve();
        }

        busy = true;

        return pdf.getPage(pageNumber).then(function (page) {
            var base = page.getViewport({ scale: 1 });
            var scale = Math.min(view.clientWidth - 2, 900) / base.width;
            var viewport = page.getViewport({ scale: scale * ratio });

            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            canvas.style.width = Math.floor(viewport.width / ratio) + 'px';
            canvas.style.height = Math.floor(viewport.height / ratio) + 'px';

            return page.render({ canvasContext: context, viewport: viewport }).promise;
        }).then(function () {
            info.textContent = 'Halaman ' + pageNumber + ' / ' + pdf.numPages;
            prev.disabled = pageNumber <= 1;
            next.disabled = pageNumber >= pdf.numPages;
        }).catch(function (error) {
            info.textContent = 'Halaman gagal ditampilkan'
                + (error && error.message ? ' (' + error.message + ')' : '')
                + '.';
        }).then(function () {
            busy = false;
        });
    }

    prev.addEventListener('click', function () {
        if (pdf && !busy && pageNumber > 1) {
            pageNumber -= 1;
            draw();
        }
    });

    next.addEventListener('click', function () {
        if (pdf && !busy && pageNumber < pdf.numPages) {
            pageNumber += 1;
            draw();
        }
    });

    // Memblokir simpan/cetak halaman reader dan menu klik kanan pada halaman ebook.
    view.addEventListener('contextmenu', function (event) {
        event.preventDefault();
    });

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && ['s', 'p'].indexOf(event.key.toLowerCase()) !== -1) {
            event.preventDefault();
        }
    });

    var resizeTimer;

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(draw, 200);
    });

    // Berkas diambil lewat fetch lebih dulu agar status HTTP dan galat
    // pemuatan bisa ditampilkan, bukan sekadar pesan kosong.
    fetch(view.dataset.src, { credentials: 'same-origin' })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            return response.arrayBuffer();
        })
        .then(function (buffer) {
            return pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise;
        })
        .then(function (document) {
            pdf = document;
            return draw();
        })
        .catch(function (error) {
            info.textContent = 'Ebook gagal dimuat'
                + (error && error.message ? ' (' + error.message + ')' : '')
                + '.';
        });
})();
