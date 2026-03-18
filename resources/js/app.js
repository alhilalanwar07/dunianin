import './bootstrap';
import { polyfill } from "mobile-drag-drop";
import { scrollBehaviourDragImageTranslateOverride } from "mobile-drag-drop/scroll-behaviour";

// Add polyfill for HTML5 drag and drop on mobile touch devices
polyfill({
    // use this to make use of the scroll behaviour
    dragImageTranslateOverride: scrollBehaviourDragImageTranslateOverride
});

// prevent default panning when dragging on touch devices
window.addEventListener('touchmove', function() {}, {passive: false});
