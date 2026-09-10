(function () {
    // Wires up the drag-and-drop feedback and chosen-filename display for
    // .ps-dropzone (style.css) -- the real <input type="file"> already
    // covers the whole box (position:absolute; opacity:0) so clicking
    // and choosing a file works without this script; this only adds the
    // visual confirmation of which file got picked and the dashed-border
    // highlight while dragging a file over the box.
    document.querySelectorAll('[data-dropzone]').forEach(zone => {
        const input = zone.querySelector('[data-dropzone-input]');
        const filename = zone.querySelector('[data-dropzone-filename]');
        if (!input || !filename) return;

        const showFile = () => {
            const file = input.files && input.files[0];
            filename.textContent = file ? file.name : 'No file chosen';
        };

        input.addEventListener('change', showFile);

        ['dragenter', 'dragover'].forEach(type => {
            zone.addEventListener(type, event => {
                event.preventDefault();
                zone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(type => {
            zone.addEventListener(type, event => {
                event.preventDefault();
                zone.classList.remove('is-dragover');
            });
        });
        zone.addEventListener('drop', event => {
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            input.files = event.dataTransfer.files;
            showFile();
            input.dispatchEvent(new Event('change'));
        });

        showFile();
    });
})();
