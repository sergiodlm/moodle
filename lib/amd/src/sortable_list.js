// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A javascript module to handle list items drag and drop
 *
 * Example of usage:
 *
 * define(['jquery', 'core/sortable_list'], function($, sortableList) {
 *     sortableList.init({
 *         listSelector: 'ul.my-awesome-list', // mandatory, CSS selector for the list (usually <ul> or <tbody>)
 *         moveHandlerSelector: '.draghandle'  // optional but recommended, CSS selector of the crossarrow handle
 *     });
 *     $('ul.my-awesome-list > *').on('sortablelist-drop', function(evt, info) {
 *         console.log(info);
 *     });
 * }
 *
 * For the full list of possible parameters see var defaultParameters below.
 *
 * The following jQuery events are fired:
 * - sortablelist-dragstart : when user started dragging a list element
 * - sortablelist-drag : when user dragged a list element to a new position
 * - sortablelist-drop : when user dropped a list element
 * - sortablelist-dragend : when user finished dragging - either fired right after dropping or
 *                          if "Esc" was pressed during dragging
 *
 * @module     core/sortable_list
 * @class      sortable_list
 * @package    core
 * @copyright  2018 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/autoscroll', 'core/str', 'core/modal_factory', 'core/modal_events'],
function($, log, autoScroll, str, ModalFactory, ModalEvents) {

    /**
     * Default parameters
     *
     * @type {Object}
     */
    var defaultParameters = {
        listSelector: null, /* CSS selector for sortable lists, must be specified during initialization. */
        moveHandlerSelector: null, /* CSS selector for a drag handle. By default the whole item is a handle. */
        isHorizontal: false, /* Set to true if the list is horizontal. */
        destinationNameCallback: null, /* Callback that returns a string or Promise with the label for the move destination. */
        elementNameCallback: null, /* Should return a string or Promise. Used for move dialogue title and destination name. */
        moveDialogueTitleCallback: null /* Should return a string or Promise. Used to form move dialogue title. */
    };

    /**
     * Class names for different elements that may be changed during sorting
     *
     * @type {Object}
     */
    var CSS = {
        keyboardDragClass: 'dragdrop-keyboard-drag', /* Class of the list of destinations in the popup */
        isDraggedClass: 'sortable-list-is-dragged', /* Class added to the element that is dragged. */
        currentPositionClass: 'sortable-list-current-position', /* Class added to the current position of a dragged element. */
        sourceListClass: 'sortable-list-source', /* Class added to the list where dragging was started from. */
        targetListClass: 'sortable-list-target', /* Class added to all lists where item can be dropped. */
        overElementClass: 'sortable-list-over-element' /* Class added to the list element when the dragged element is above it. */
    };

    /**
     * Stores parameters of the currently dragged item
     *
     * @type {Object}
     */
    var params = {};

    /**
     * Stores information about currently dragged item
     *
     * @type {Object}
     */
    var info = {};

    /**
     * Stores the proxy object
     *
     * @type {jQuery}
     */
    var proxy;

    /**
     * Stores initial position of the proxy
     *
     * @type {Object}
     */
    var proxyDelta;

    /**
     * Counter of drag events
     *
     * @type {Number}
     */
    var dragCounter = 0;

    /**
     * Resets the temporary classes assigned during dragging
     */
    var resetDraggedClasses = function() {
        var lists = $(params.listSelector);
        lists.children()
            .removeClass(params.isDraggedClass)
            .removeClass(params.currentPositionClass)
            .removeClass(params.overElementClass);
        lists
            .removeClass(params.targetListClass)
            .removeClass(params.sourceListClass);
    };

    /**
     * {Event} stores the last event that had pageX and pageY defined
     */
    var lastEvent;

    /**
     * Calculates evt.pageX, evt.pageY, evt.clientX and evt.clientY
     *
     * For touch events pageX and pageY are taken from the first touch;
     * For the emulated mousemove event they are taken from the last real event.
     *
     * @param {Event} evt
     */
    var calculatePositionOnPage = function(evt) {

        if (evt.originalEvent && evt.originalEvent.touches && evt.originalEvent.touches[0] !== undefined) {
            // This is a touchmove or touchstart event, get position from the first touch position.
            var touch = evt.originalEvent.touches[0];
            evt.pageX = touch.pageX;
            evt.pageY = touch.pageY;
        }

        if (evt.pageX === undefined) {
            // Information is not present in case of touchend or when event was emulated by autoScroll.
            // Take the absolute mouse position from the last event.
            evt.pageX = lastEvent.pageX;
            evt.pageY = lastEvent.pageY;
        } else {
            lastEvent = evt;
        }

        if (evt.clientX === undefined) {
            // If not provided in event calculate relative mouse position.
            evt.clientX = Math.round(evt.pageX - $(window).scrollLeft());
            evt.clientY = Math.round(evt.pageY - $(window).scrollTop());
        }
    };

    /**
     * Handler from dragstart event
     *
     * @param {Event} evt
     */
    var dragstartHandler = function(evt) {
        params = evt.data.params;
        resetDraggedClasses();

        calculatePositionOnPage(evt);
        var movedElement = $(evt.currentTarget);

        // Check that we grabbed the element by the handle.
        if (params.moveHandlerSelector !== null) {
            if (!$(evt.target).closest(params.moveHandlerSelector, movedElement).length) {
                return;
            }
        }

        evt.stopPropagation();
        evt.preventDefault();

        // Information about moved element with original location.
        // This object is passed to all registered callbacks (onDrop, onDragStart, onMove, onDragCancel).
        dragCounter++;
        info = {
            draggedElement: movedElement,
            sourceNextElement: movedElement.next(),
            sourceList: movedElement.parent(),
            targetNextElement: movedElement.next(),
            targetList: movedElement.parent(),
            type: evt.type,
            dropped: false,
            startX: evt.pageX,
            startY: evt.pageY,
            startTime: new Date().getTime()
        };

        $(params.listSelector).addClass(params.targetListClass);

        var offset = movedElement.offset();
        movedElement.addClass(params.currentPositionClass);
        proxyDelta = {x: offset.left - evt.pageX, y: offset.top - evt.pageY};
        proxy = $();
        var thisDragCounter = dragCounter;
        setTimeout(function() {
            if (info === null || info.type === 'click' || dragCounter !== thisDragCounter) {
                return;
            }

            // Create a proxy - the copy of the dragged element that moves together with a mouse.
            createProxy();
        }, 500);

        // Start drag.
        $('body').on('mousemove touchmove mouseup touchend', dragHandler);
        $('body').on('keypress', dragcancelHandler);

        // Start autoscrolling. Every time the page is scrolled emulate the mousemove event.
        autoScroll.start(function() {
            $('body').trigger('mousemove');
        });

        executeCallback('dragstart');
    };

    /**
     * Creates a "proxy" object - a copy of the element that is being moved that always follows the mouse
     */
    var createProxy = function() {
        proxy = info.draggedElement.clone();
        info.sourceList.append(proxy);
        proxy.removeAttr('id').removeClass(params.currentPositionClass)
            .addClass(params.isDraggedClass).css({position: 'fixed'});
        proxy.offset({top: proxyDelta.y + lastEvent.pageY, left: proxyDelta.x + lastEvent.pageX});
    };

    /**
     * Handler for click event - when user clicks on the drag handler or presses Enter on keyboard
     *
     * @param {Event} evt
     */
    var clickHandler = function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        params = evt.data.params;

        // Find the element that this draghandle belongs to.
        var sourceList = $(evt.currentTarget).closest(params.listSelector),
            movedElement = sourceList.children().filter(function() {
                return $.contains(this, evt.currentTarget);
            });
        if (!movedElement.length) {
            return;
        }

        // Store information about moved element with original location.
        dragCounter++;
        info = {
            draggedElement: movedElement,
            sourceNextElement: movedElement.next(),
            sourceList: sourceList,
            targetNextElement: movedElement.next(),
            targetList: sourceList,
            dropped: false,
            type: evt.type,
            startTime: new Date().getTime()
        };

        executeCallback('dragstart');
        displayMoveDialogue();
    };

    /**
     * Finds the position of the mouse inside the element - on the top, on the bottom, on the right or on the left\
     *
     * Used to determine if the moved element should be moved after or before the current element
     *
     * @param {Number} pageX
     * @param {Number} pageY
     * @param {jQuery} element
     * @returns {Object}|null
     */
    var getPositionInNode = function(pageX, pageY, element) {
        if (!element.length) {
            return null;
        }
        var node = element[0],
            offset = 0,
            rect = node.getBoundingClientRect(),
            y = pageY - (rect.top + window.scrollY),
            x = pageX - (rect.left + window.scrollX);
        if (x >= -offset && x <= rect.width + offset && y >= -offset && y <= rect.height + offset) {
            return {
                x: x,
                y: y,
                xRatio: rect.width ? (x / rect.width) : 0,
                yRatio: rect.height ? (y / rect.height) : 0
            };
        }
        return null;
    };

    /**
     * Callback for filter that checks that current element is not proxy
     *
     * @return {boolean}
     */
    var isNotProxy = function() {
        return !proxy || !proxy.length || this !== proxy[0];
    };

    /**
     * Handler for events mousemove touchmove mouseup touchend
     *
     * @param {Event} evt
     */
    var dragHandler = function(evt) {

        calculatePositionOnPage(evt);

        // We can not use evt.target here because it will most likely be our proxy.
        // Move the proxy out of the way so we can find the element at the current mouse position.
        proxy.offset({top: -1000, left: -1000});
        // Find the element at the current mouse position.
        var element = $(document.elementFromPoint(evt.clientX, evt.clientY));

        // Find the list element and the list over the mouse position.
        var current = element.closest('.' + params.targetListClass + ' > :not(.' + params.isDraggedClass + ')'),
            currentList = element.closest('.' + params.targetListClass);

        // Add the specified class to the list element we are hovering.
        $('.' + params.overElementClass).removeClass(params.overElementClass);
        current.addClass(params.overElementClass);

        // Move proxy to the current position.
        proxy.offset({top: proxyDelta.y + evt.pageY, left: proxyDelta.x + evt.pageX});

        if (currentList.length && !currentList.children().filter(isNotProxy).length) {
            // Mouse is over an empty list.
            moveDraggedElement(currentList, $());
        } else if (current.length === 1) {
            // Mouse is over an element in a list - find whether we should move the current position
            // above or below this element.
            var coordinates = getPositionInNode(evt.pageX, evt.pageY, current);
            if (coordinates) {
                var parent = current.parent(),
                    ratio = params.isHorizontal ? coordinates.xRatio : coordinates.yRatio;
                if (ratio > 0.5) {
                    // Insert after this element.
                    moveDraggedElement(parent, current.next().filter(isNotProxy));
                } else {
                    // Insert before this element.
                    moveDraggedElement(parent, current);
                }
            }
        }

        if (evt.type === 'mouseup' || evt.type === 'touchend') {
            // Drop the moved element.
            info.endX = evt.pageX;
            info.endY = evt.pageY;
            info.endTime = new Date().getTime();
            info.dropped = true;
            executeCallback('drop');
            finishDragging();
        }
    };

    /**
     * Moves the current position of the dragged element
     *
     * @param {jQuery} parentElement
     * @param {jQuery} beforeElement
     */
    var moveDraggedElement = function(parentElement, beforeElement) {
        var dragEl = info.draggedElement;
        if (beforeElement.length && beforeElement[0] === dragEl[0]) {
            // Insert before the current position of the dragged element - nothing to do.
            return;
        }
        if (parentElement[0] === info.targetList[0] &&
                beforeElement.length === info.targetNextElement.length &&
                beforeElement[0] === info.targetNextElement[0]) {
            // Insert in the same location as the current position - nothing to do.
            return;
        }

        if (beforeElement.length) {
            // Move the dragged element before the specified element.
            parentElement[0].insertBefore(dragEl[0], beforeElement[0]);
        } else if (proxy && proxy.parent().length && proxy.parent()[0] === parentElement[0]) {
            // We need to move to the end of the list but the last element in this list is a proxy.
            // Always leave the proxy in the end of the list.
            parentElement[0].insertBefore(dragEl[0], proxy[0]);
        } else {
            // Insert in the end of a list (when proxy is in another list).
            parentElement[0].appendChild(dragEl[0]);
        }

        // Save the current position of the dragged element in the list.
        info.targetList = parentElement;
        info.targetNextElement = beforeElement;
        executeCallback('drag');
    };

    /**
     * Finish dragging (when dropped or cancelled).
     */
    var finishDragging = function() {
        resetDraggedClasses();
        autoScroll.stop();
        $('body').off('mousemove touchmove mouseup touchend', dragHandler);
        $('body').off('keypress', dragcancelHandler);
        if (proxy) {
            proxy.remove();
            proxy = $();
        }
        executeCallback('dragend');
        info = null;
    };

    /**
     * Executes callback specified in sortable list parameters
     *
     * @param {String} eventName
     */
    var executeCallback = function(eventName) {
        info.draggedElement.trigger('sortablelist-' + eventName, info);
    };

    /**
     * Handler from keypress event (cancel dragging when Esc is pressed)
     *
     * @param {Event} evt
     */
    var dragcancelHandler = function(evt) {
        if (evt.type !== 'keypress' || evt.originalEvent.keyCode !== 27) {
            // Only cancel dragging when Esc was pressed.
            return;
        }
        // Dragging was cancelled. Return item to the original position.
        moveDraggedElement(info.sourceList, info.sourceNextElement);
        finishDragging();
    };

    /**
     * Helper method to convert a string to a promise
     *
     * @param {(String|Promise)} value
     * @return {Promise}
     */
    var convertToPromise = function(value) {
        var p = value;
        if (typeof value !== 'object' || !value.hasOwnProperty('then')) {
            p = $.Deferred();
            p.resolve(value);
        }
        return p;
    };

    /**
     * Returns the name of the current element to be used in the move dialogue
     *
     * @param {jQuery} element
     * @return {Promise}
     */
    var getElementName = function(element) {
        if (params.elementNameCallback) {
            var name = params.elementNameCallback(element);
            if (name !== null) {
                return convertToPromise(name);
            }
        }
        return convertToPromise(element.text());
    };

    /**
     * Returns the label for the potential move destination, i.e. "After ElementX" or "To the top of the list"
     *
     * Note that we use "after" in the label for better UX
     *
     * @param {jQuery} parentElement
     * @param {jQuery} afterElement
     * @return {Promise}
     */
    var getDestinationName = function(parentElement, afterElement) {
        if (params.destinationNameCallback) {
            var name = params.destinationNameCallback(parentElement, afterElement);
            if (name !== null) {
                return convertToPromise(name);
            }
        }
        if (!afterElement.length) {
            return str.get_string('movecontenttothetop', 'moodle');
        } else {
            return getElementName(afterElement)
                .then(function(name) {
                    return str.get_string('movecontentafter', 'moodle', name);
                });
        }
    };

    /**
     * Returns the title for the move dialogue ("Move elementY")
     *
     * @param {jQuery} element
     * @return {Promise}
     */
    var getMoveDialogueTitle = function(element) {
        if (params.moveDialogueTitleCallback) {
            var name = params.moveDialogueTitleCallback(element);
            if (name !== null) {
                return convertToPromise(name);
            }
        }
        return getElementName(element).then(function(name) {
            return str.get_string('movecontent', 'moodle', name);
        });
    };

    /**
     * Returns the list of possible move destinations with their onclick handlers
     *
     * @param {Modal} modal
     * @return {jQuery}
     */
    var getDestinationsList = function(modal) {
        var targets = $(params.listSelector),
            list = $('<ul/>').addClass(params.keyboardDragClass),
            createLink = function(parentElement, beforeElement, afterElement) {
                if (beforeElement.is(info.draggedElement) || afterElement.is(info.draggedElement)) {
                    return;
                }
                var li = $('<li/>').appendTo(list);
                var a = $('<a href="#"/>')
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        moveDraggedElement(parentElement, beforeElement);
                        info.endTime = new Date().getTime();
                        info.dropped = true;
                        executeCallback('drop');
                        modal.hide();
                    })
                    .appendTo(li);
                getDestinationName(parentElement, afterElement).then(function(txt) {
                    a.text(txt);
                });
            };
        targets.each(function() {
            var node = $(this),
                children = node.children();
            node.children().each(function() {
                createLink(node, $(this), $(this).prev());
            });
            createLink(node, $(), children.last());
        });
        return list;
    };

    /**
     * Displays the dialogue to move element.
     */
    var displayMoveDialogue = function() {
        ModalFactory.create({
            type: ModalFactory.types.CANCEL
        }).done(function(modal) {
            modal.getRoot().on(ModalEvents.hidden, function() {
                // Always destroy when hidden, it is generated dynamically each time.
                modal.destroy();
                finishDragging();
            });
            modal.setTitle(getMoveDialogueTitle(info.draggedElement));
            modal.getBody().append(getDestinationsList(modal));
            modal.show();
        });
    };

    return {
        /**
         * Initialise sortable list.
         *
         * @param {Object} params Parameters for the list. See defaultParameters above for examples.
         */
        init: function(params) {
            if (typeof params.listSelector === 'undefined') {
                log.error('Parameter listSelector must be specified');
                return;
            }
            params = $.extend({}, defaultParameters, CSS, params);
            $(params.listSelector).on('mousedown touchstart', '> *', {params: params}, dragstartHandler);
            if (params.moveHandlerSelector !== null) {
                $(params.listSelector).on('click', params.moveHandlerSelector, {params: params}, clickHandler);
            }
        }
    };
});
