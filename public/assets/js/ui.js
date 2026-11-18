// Navigasi aplikasi dan aksi dua langkah.

// Rel navigasi pada layar sempit.

// Pesan sukses disimpan singkat agar tetap terlihat setelah halaman dimuat
// ulang oleh aksi yang mengubah data.
window.flashMessage = function (text, success) {
    'use strict';

    try {
        sessionStorage.setItem('flashMessage', JSON.stringify({
            text: text,
            success: !!success
        }));
    } catch (error) {
        // Mode privat bisa memblokir sessionStorage; pesan hanya hilang.
    }
};

(function () {
    'use strict';

    var raw = null;

    try {
        raw = sessionStorage.getItem('flashMessage');
        sessionStorage.removeItem('flashMessage');
    } catch (error) {
        return;
    }

    if (!raw) {
        return;
    }

    var box = document.getElementById('message');

    if (!box) {
        return;
    }

    try {
        var data = JSON.parse(raw);
        box.textContent = data.text;
        box.className = 'notice ' + (data.success ? 'notice-success' : 'notice-error');
        box.hidden = false;
    } catch (error) {
        // Pesan rusak diabaikan.
    }
})();

// Rel navigasi pada layar sempit.
(function () {
    'use strict';

    var body = document.body;
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('mainNav');

    if (!toggle || !nav) {
        return;
    }

    var scrim = document.querySelector('[data-nav-close]');
    var firstLink = nav.querySelector('a');

    function setOpen(open) {
        body.dataset.nav = open ? 'open' : 'closed';
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggle.addEventListener('click', function () {
        var open = body.dataset.nav !== 'open';
        setOpen(open);

        if (open && firstLink) {
            firstLink.focus();
        }
    });

    if (scrim) {
        scrim.addEventListener('click', function () {
            setOpen(false);
            toggle.focus();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.dataset.nav === 'open') {
            setOpen(false);
            toggle.focus();
        }
    });

    nav.addEventListener('click', function (event) {
        if (event.target.closest('a')) {
            setOpen(false);
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && body.dataset.nav === 'open') {
            setOpen(false);
        }
    });
})();

// Aksi berisiko: klik pertama mengubah label tombol, klik kedua menjalankan.
// Mengembalikan true hanya pada klik kedua, sehingga pemanggil dapat langsung
// memakai `if (!window.armAction(this, 'Yakin?')) return;`.
window.armAction = function (button, message) {
    'use strict';

    if (!button) {
        return false;
    }

    var timer = Number(button.dataset.armTimer || 0);

    if (button.dataset.armed === '1') {
        window.clearTimeout(timer);
        button.dataset.armed = '0';
        button.classList.remove('is-armed');
        button.innerHTML = button.dataset.armLabel;
        return true;
    }

    if (button.dataset.armLabel === undefined) {
        button.dataset.armLabel = button.innerHTML;
    }

    button.dataset.armed = '1';
    button.classList.add('is-armed');
    button.textContent = message;

    button.dataset.armTimer = String(window.setTimeout(function () {
        button.dataset.armed = '0';
        button.classList.remove('is-armed');
        button.innerHTML = button.dataset.armLabel;
    }, 4000));

    return false;
};

// Pembaca: geser bar halaman agar menempel tepat di bawah bilah judul,
// berapa pun tinggi bilah itu saat teksnya membungkus.
(function () {
    'use strict';

    var top = document.querySelector('.reader-top');

    if (!top) {
        return;
    }

    function setOffset() {
        document.documentElement.style.setProperty(
            '--reader-top',
            top.offsetHeight + 'px'
        );
    }

    if (window.ResizeObserver) {
        new ResizeObserver(setOffset).observe(top);
    }

    window.addEventListener('resize', setOffset);
    setOffset();
})();
