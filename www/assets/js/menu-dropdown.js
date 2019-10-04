/*Toggle dropdown list*/
/*https://gist.github.com/slavapas/593e8e50cf4cc16ac972afcbad4f70c8*/

var navMenuDiv = document.getElementById("nav-content");
var navMenu = document.getElementById("nav-toggle");
var parallax = document.getElementById("parallax");
document.onclick = check;

function check(e) {
    var target = (e && e.target) || (event && event.srcElement);


    //Nav Menu
    if (!checkParent(target, navMenuDiv)) {
        // click NOT on the menu
        if (checkParent(target, navMenu)) {
            // click on the link
            if (navMenuDiv.classList.contains("hidden")) {
                navMenuDiv.classList.remove("hidden");
                parallax.style.overflowY = 'hidden';
            } else {
                navMenuDiv.classList.add("hidden");
                parallax.style.overflowY = 'auto';
            }
        } else {
            // click both outside link and outside menu, hide menu
            navMenuDiv.classList.add("hidden");
            parallax.style.overflowY = 'auto';
        }
    }
}

function checkParent(t, elm) {
    while (t.parentNode) {
        if (t == elm) { return true; }
        t = t.parentNode;
    }
    return false;
}

