import './bootstrap'
import ScrollMagic from "scrollmagic/scrollmagic/minified/ScrollMagic.min";

let controller = new ScrollMagic.Controller();

const showAll = (window.showAll = () => {
    let all = document.querySelectorAll(".post-box");
    all.forEach(function(el, idx) {
        new ScrollMagic.Scene({
            triggerElement: idx == 0 ? el : all[idx - 1],
            // triggerHook: 0.9,
            offset: -500, // move trigger to center of element
            reverse: false // only do once
        })
            .setClassToggle(el, "visible") // add class toggle
            // .addIndicators() // add indicators (requires plugin)
            .addTo(controller);
    });
});

jQuery(function() {
    showAll();
});

let imanok = function() {
    let all = document.querySelectorAll(".post-box");
    all.forEach(item => {
        let rect = item.getBoundingClientRect();
        let w = parseFloat(jQuery(window).width());
        let orientation;
        if (rect.left < w / 2 / 2 - 10) {
            orientation = "left";
        } else {
            orientation = "right";
        }
        if (!jQuery(item).hasClass("kon")) {
            jQuery(item)
                .addClass("kon")
                .addClass(orientation);
            document.querySelector('.grid-cols-blog-list-mobile').classList.remove('xl:grid-cols-blog-list')
            document.getElementsByClassName(
                "maincontainer"
            )[0].__x.$data.gem = true;
        } else {
            jQuery(item)
                .removeClass("kon")
                .removeClass("left")
                .removeClass("right");
            document.querySelector('.grid-cols-blog-list-mobile').classList.add('xl:grid-cols-blog-list')
            document.getElementsByClassName(
                "maincontainer"
            )[0].__x.$data.gem = false;
        }
    });
};

let pattern = [
    "ArrowUp",
    "ArrowUp",
    "ArrowDown",
    "ArrowDown",
    "ArrowLeft",
    "ArrowRight",
    "ArrowLeft",
    "ArrowRight",
    "b",
    "a"
];
let current = 0;

let keyHandler = function(event) {
    // If the key isn't in the pattern, or isn't the current key in the pattern, reset
    if (pattern.indexOf(event.key) < 0 || event.key !== pattern[current]) {
        current = 0;
        return;
    }
    // Update how much of the pattern is complete
    current++;
    // If complete, alert and reset
    if (pattern.length === current) {
        current = 0;
        imanok();
        document.addEventListener("keydown", handler);
    }
};

// Listen for keydown events
document.addEventListener("keydown", keyHandler, false);

// handler function
function handler(e) {
    console.log(e.key);
    if (jQuery(".post-box.kon").length > 0 && e.key == "Escape") {
        // remove this handler
        document.removeEventListener("keydown", handler);
        imanok();
    }
}

jQuery(document).on("click", ".prevent-if", function(e) {
    // e.preventDefault();
    e.stopPropagation();
    let parentApp = jQuery(this).parent(".parentapp")[0];
    if(!parentApp) return;
    let data = parentApp?.['_x_dataStack']?.[0];
    if(!data) return;
    // alert(data.show ? "da" : "nu");
    if (!data.show) {
        e.preventDefault();
        data.show = true;
    }
});

function setCookie(cname, cvalue, exdays) {
    let d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

window.expandiste = () => {
    let main = jQuery(".maincontainer");
    if (main.hasClass("containerfull")) {
        setCookie("containerfull", "nu", 60);
        main.removeClass("containerfull").addClass("container");
    } else {
        setCookie("containerfull", "da", 60);
        main.addClass("containerfull").removeClass("container");
    }
};
