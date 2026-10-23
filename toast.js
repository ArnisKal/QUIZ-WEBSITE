(function () {
    var box = document.createElement('div');
    box.className = 'toast_container';
    document.body.appendChild(box);

    window.showToast = function (text, type) {
        var t = document.createElement('div');
        t.className = 'toast' + (type === 'error' ? ' error' : '');
        t.textContent = text;
        box.appendChild(t);
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                t.classList.add('show');
            });
        });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { t.remove(); }, 300);
        }, 2000);
    };
    document.querySelectorAll('.flash_ok').forEach(function (el) {
        showToast(el.textContent.trim());
        el.remove();
    });
})();