document.getElementById('dnDismiss').addEventListener('click', function () { this.parentElement.hidden = true; });
const note = document.getElementById('donationNote');
note.addEventListener('input', () => { document.getElementById('donationNoteCount').textContent = note.value.length + '/500'; });
