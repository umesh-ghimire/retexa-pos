// Fix: move all Bootstrap modals to be direct children of <body>.
// This is required because Otika's sidebar wrapper uses CSS "transform"
// for its collapse animation, which breaks position:fixed modals nested
// inside it (causing clicks/typing to be silently swallowed).
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".modal").forEach(function (modal) {
        document.body.appendChild(modal);
    });
});

/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 * 
 */

"use strict";

