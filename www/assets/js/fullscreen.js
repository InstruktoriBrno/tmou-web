// Toggling content to fullscreen and back
$(document).on('click', '.toggle-fullscreen', function () {

    const altLabel = this.getAttribute('data-alternative-label');

    const originalContent = this.innerHTML;
    this.innerHTML = altLabel;
    this.setAttribute('data-alternative-label', originalContent);


    const isHidden = document.querySelector('#navigation').classList.contains('hidden');
    toggleFullscreenContent(isHidden);
    localStorage.setItem('fullscreen', isHidden ? 'false' : 'true');
});

$(function () {
    const fullscreen = localStorage.getItem('fullscreen');
    if (fullscreen === 'true') {
        toggleFullscreenContent(false);
    }
});


function toggleFullscreenContent(state) {
    if (!state) {
        document.querySelector('#navigation').classList.add('hidden');
        document.querySelector('#content').classList.remove('lg:w-4/5');
        document.querySelector('#content').classList.remove('lg:pl-4');
        document.querySelector('#content').parentElement.classList.remove('container');
        document.querySelector('footer').classList.add('hidden');
    } else {
        document.querySelector('#navigation').classList.remove('hidden');
        document.querySelector('#content').classList.add('lg:w-4/5');
        document.querySelector('#content').classList.add('lg:pl-4');
        document.querySelector('#content').parentElement.classList.add('container');
        document.querySelector('footer').classList.remove('hidden');
    }
}
