// Basic functions
var fnmap = {
    'toggle': 'toggle',
    'show': 'add',
    'hide': 'remove'
};
function toArray(items) {
    var output = [];
    items.forEach(function (item) {
        output.push(item);
    });
    return output;
}

function  collapse (selector, cmd, toggleElement) {
    var targets = toArray(document.querySelectorAll(selector));
    targets.forEach(function(target) {
        target.classList[fnmap[cmd]]('show');
        if (target.classList.contains('show')) {
            toggleElement.innerHTML = 'Skrýt' + toggleElement.getAttribute('title');
        } else {
            toggleElement.innerHTML = 'Zobrazit' + toggleElement.getAttribute('title');
        }
    });
}

// Grab all the trigger elements on the page
const triggers = toArray(document.querySelectorAll('.collapse-toggle'));

// Add listener for clicking on triggers
window.addEventListener('click', function (ev) {
    var elm = ev.target;
    if (triggers.includes(elm)) {
        var selector = elm.getAttribute('href');
        collapse(selector, 'toggle', elm);
        ev.preventDefault();
        return false;
    }
}, false);
