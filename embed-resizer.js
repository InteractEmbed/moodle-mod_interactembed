(function() {
    'use strict';
    if (window.modInteractEmbedResizerInitialised) {
        return;
    }
    window.modInteractEmbedResizerInitialised = true;
    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin || !event.data || event.data.type !== 'mod_interactembed:resize') {
            return;
        }
        var cmid = Number(event.data.cmid);
        var height = Number(event.data.height);
        if (!Number.isInteger(cmid) || !Number.isFinite(height) || height < 200 || height > 4000) {
            return;
        }
        document.querySelectorAll('iframe[data-interactembed-id="' + cmid + '"]').forEach(function(frame) {
            if (frame.contentWindow === event.source) {
                frame.style.height = Math.ceil(height) + 'px';
                frame.setAttribute('height', String(Math.ceil(height)));
            }
        });
    });
}());
