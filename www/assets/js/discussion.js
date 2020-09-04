const body = $('body');

// Inserting function and binding
function insertAtCursor2 (input, textToInsert) {
    // get current text of the input
    const value = input.value;

    // save selection start and end position
    const start = input.selectionStart;
    const end = input.selectionEnd;

    // update the value with our text inserted
    input.value = value.slice(0, start) + textToInsert + value.slice(end);

    // update cursor to be at the end of insertion
    input.selectionStart = input.selectionEnd = start + textToInsert.length;
}

body.on('click', '.insert-discussion-ref', function () {
    const refId = this.getAttribute('data-id');
    if (!refId) {
        return;
    }

    const textToInsert = '[' + refId + '] ';

    const input = document.querySelector('textarea');
    insertAtCursor2(input, textToInsert);
});

body.on('click', '.insert-discussion-quote', function () {
    const quotedContent = this.getAttribute('data-content');
    if (!quotedContent) {
        return;
    }

    const textToInsert = quotedContent.replace(/^/gm, '> ') + '\n';

    const input = document.querySelector('textarea');
    insertAtCursor2(input, textToInsert);
});

body.on('click', '.copy-link-to-clipboard', function () {
    const link = this.getAttribute('data-content');
    if (!link) {
        return;
    }
    const el = document.createElement('textarea');
    el.value = link;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);

    alert('Odkaz byl zkopírován do schránky.');
});

$(function(){
    var focusablePost = $('.post-focus');
    if (!focusablePost.length) {
        return;
    }
    $("#parallax").animate({
        scrollTop: focusablePost.offset().top - 300 // Offset due to menu and previous post visibility
    }, 400);
});
