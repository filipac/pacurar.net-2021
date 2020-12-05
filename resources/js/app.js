require("./bootstrap");
import ScrollMagic from "scrollmagic";

var controller = new ScrollMagic.Controller();

jQuery(function() {
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

let konami = function() {
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
            document.getElementsByClassName(
                "maincontainer"
            )[0].__x.$data.gem = true;
        } else {
            jQuery(item)
                .removeClass("kon")
                .removeClass("left")
                .removeClass("right");
            document.getElementsByClassName(
                "maincontainer"
            )[0].__x.$data.gem = false;
        }
    });
};

var pattern = [
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
var current = 0;

var keyHandler = function(event) {
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
        konami();
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
        konami();
    }
}

jQuery(document).on("click", ".prevent-if", function(e) {
    // e.preventDefault();
    e.stopPropagation();
    let data = jQuery(this).parent(".parentapp")[0].__x.$data;
    // alert(data.show ? "da" : "nu");
    if (!data.show) {
        e.preventDefault();
        data.show = true;
    }
});
jQuery(document).on("click", ".menu-item-has-children", function(e) {
    // e.preventDefault();
    let data = this.__x.$data;
    if (!data.open) {
        e.preventDefault();
        data.open = true;
    }
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

window.expandiste = () => {
    let main = jQuery(".maincontainer");
    if (main.hasClass("containerfull")) {
        document.cookie =
            "containerfull=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        main.removeClass("containerfull").addClass("container");
    } else {
        setCookie("containerfull", "1", 60);
        main.addClass("containerfull").removeClass("container");
    }
};
