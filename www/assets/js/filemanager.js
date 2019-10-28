// Collapsing and uncollapsing directories
$(document).on('click', '.caret', function () {
    this.parentElement.parentElement.querySelector(".nested").classList.toggle("active");
    this.classList.toggle("caret-down");
});

// Inserting function and binding
function insertAtCursor (input, textToInsert) {
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

function setEditedFile(basename, subdir)
{
    document.querySelector('#frm-changeFileForm-original').setAttribute('value', basename);
    document.querySelector('#frm-changeFileForm-name').setAttribute('value', basename);
    $("#frm-changeFileForm-targetDir").val(subdir);
    document.querySelector('#frm-changeFileForm-name').focus();
    document.querySelector(".column-files").scrollTo(0, document.querySelector(".column-files").scrollHeight);
    return false;
}

$(document).on('click', '.insert-as-image', function () {
    if (!window.opener) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }
    var textArea = window.opener.document.querySelector('#' + window.fileManagerTargetId);
    if (!textArea) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }
    var src = this.getAttribute('data-src');
    var text = this.getAttribute('data-text');

    insertAtCursor(textArea, '[* ' + src + ' .(' + text + ') *]');
    textArea.focus();
    window.close();
});

$(document).on('click', '.insert-as-link', function () {
    if (!window.opener) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }
    var textArea = window.opener.document.querySelector('#' + window.fileManagerTargetId);
    if (!textArea) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }

    var src = this.getAttribute('data-src');
    var text = this.getAttribute('data-text');
    if (text === null) {
        text = prompt("Zadejte text odkazu");
        if (text === null) {
            return;
        }
    }

    insertAtCursor(textArea, '"' + text + ' .{target:_blank}":' + src);
    textArea.focus();
    window.close();
});
$(document).on('click', '.insert-as-image-link', function () {
    if (!window.opener) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }
    var textArea = window.opener.document.querySelector('#' + window.fileManagerTargetId);
    if (!textArea) {
        alert('Okno ze kterého byl tento správce souborů otevřen bylo již zavřeno nebo tato stránka ručně obnovena. Zavřete toto okno a opakujte akci v nově otevřeném.');
        return;
    }

    var src = this.getAttribute('data-src');
    var text = this.getAttribute('data-text');

    insertAtCursor(textArea, '"[* ' + src + ' .(' + text + ') *] .{target:_blank}":' + src);
    textArea.focus();
    window.close();
});
