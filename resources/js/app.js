import './bootstrap';
import { polyfill } from "mobile-drag-drop";
import { scrollBehaviourDragImageTranslateOverride } from "mobile-drag-drop/scroll-behaviour";
import "mobile-drag-drop/default.css"; // Wajib untuk styling default polyfill

// Add polyfill for HTML5 drag and drop on mobile touch devices
polyfill({
    // use this to make use of the scroll behaviour
    dragImageTranslateOverride: scrollBehaviourDragImageTranslateOverride
});

// prevent default panning when dragging on touch devices
// only apply to elements with 'touch-none' class so we don't break scrolling on the whole page
window.addEventListener('touchmove', function(event) {
    if (event.target.closest('.touch-none')) {
        event.preventDefault();
    }
}, {passive: false});
