javascript:window.addEventListener('keydown', function(event) {
    // F2 key or \
    var isValidKey = event.keyCode == 113 || event.keyCode == 220;
    if (!isValidKey) {
        return;
    }
    var currentEntry = document.getElementById('current-entry');
    var title = document.querySelector('#current-entry .entry-title-link').firstChild.nodeValue;
    var tag = document.querySelector('#current-entry .entry-tagging-action-title');
    var evt = document.createEvent("MouseEvents");
    evt.initMouseEvent("click", true, true, window,
                        0, 0, 0, 0, 0, false, false, false, false, 0, null);
    tag.dispatchEvent(evt);
    setTimeout(function() {
        var m = title.match(/^(.*?)\s[-\u2013|~@]/);
        if (m && m[1]) {
            title = m[1];
        }
        var value = document.querySelector('.tags-edit-tags').value;
        if (value.indexOf(title) >= 0) {
            return;
        }
        if (value) {
            value += ' ';
        }
        document.querySelector('.tags-edit-tags').value = value + title;
    }, 10);
}, true);
