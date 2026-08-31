// Konami code -> Geek mode
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

function toggleGeekMode() {
    const html = document.documentElement;
    const isGeek = html.classList.toggle('geek-mode');

    // Also enable dark mode when activating geek mode
    if (isGeek) {
        html.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
        // Update Alpine state if available
        if (html._x_dataStack) {
            html._x_dataStack[0].darkMode = true;
            html._x_dataStack[0].geekMode = true;
        }
    } else {
        if (html._x_dataStack) {
            html._x_dataStack[0].geekMode = false;
        }
    }

    localStorage.setItem('geekMode', isGeek ? 'true' : 'false');
}

document.addEventListener("keydown", function (event) {
    if (pattern.indexOf(event.key) < 0 || event.key !== pattern[current]) {
        current = 0;
        return;
    }
    current++;
    if (pattern.length === current) {
        current = 0;
        toggleGeekMode();
    }
}, false);

// Escape to exit geek mode
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && document.documentElement.classList.contains('geek-mode')) {
        toggleGeekMode();
    }
});

// Ctrl/Cmd + Shift + R -> random post
document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === "/") {
        e.preventDefault();
        window.location.href = "/random";
    }
});
