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

/*
 * JavaScript to provide automatic scrolling, e.g. during a drag operation.
 *
<<<<<<< HEAD
 * @package core
 * @copyright 2016 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module core/autoscroll
=======
 * Note: this module is defined statically. It is a singleton. You
 * can only have one use of it active at any time. However, since this
 * is usually used in relation to drag-drop, and since you only ever
 * drag one thing at a time, this is not a problem in practice.
 *
 * @module     core/autoscroll
 * @class      autoscroll
 * @package    core
 * @copyright  2016 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
 */
define(['jquery'], function($) {
    /**
     * @alias module:core/autoscroll
     */
<<<<<<< HEAD
    var t = {
=======
    var autoscroll = {
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
        /**
         * Size of area near edge of screen that triggers scrolling.
         * @private
         */
<<<<<<< HEAD
        SCROLL_THRESHOLD : 30,
=======
        SCROLL_THRESHOLD: 30,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * How frequently to scroll window.
         * @private
         */
<<<<<<< HEAD
        SCROLL_FREQUENCY : 1000 / 60,
=======
        SCROLL_FREQUENCY: 1000 / 60,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * How many pixels to scroll per unit (1 = max scroll 30).
         * @private
         */
<<<<<<< HEAD
        SCROLL_SPEED : 0.5,
=======
        SCROLL_SPEED: 0.5,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * Set if currently scrolling up/down.
         * @private
         */
<<<<<<< HEAD
        scrollingId : null,
=======
        scrollingId: null,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * Speed we are supposed to scroll (range 1 to SCROLL_THRESHOLD).
         * @private
         */
<<<<<<< HEAD
        scrollAmount : 0,
=======
        scrollAmount: 0,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * Optional callback called when it scrolls
         * @private
         */
<<<<<<< HEAD
        callback : null,
=======
        callback: null,
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20

        /**
         * Starts automatically scrolling if user moves near edge of window.
         * This should be called in response to mouse down or touch start.
         *
         * @public
<<<<<<< HEAD
         * @param callback Optional callback that is called every time it scrolls
         */
        start : function(callback) {
            $(window).on('mousemove', t.mouseMove);
            $(window).on('touchmove', t.touchMove);
            t.callback = callback;
=======
         * @param {Function} callback Optional callback that is called every time it scrolls
         */
        start: function(callback) {
            $(window).on('mousemove', autoscroll.mouseMove);
            $(window).on('touchmove', autoscroll.touchMove);
            autoscroll.callback = callback;
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
        },

        /**
         * Stops automatically scrolling. This should be called in response to mouse up or touch end.
         *
         * @public
         */
<<<<<<< HEAD
        stop : function() {
            $(window).off('mousemove', t.mouseMove);
            $(window).off('touchmove', t.touchMove);
            if (t.scrollingId !== null) {
                t.stopScrolling();
=======
        stop: function() {
            $(window).off('mousemove', autoscroll.mouseMove);
            $(window).off('touchmove', autoscroll.touchMove);
            if (autoscroll.scrollingId !== null) {
                autoscroll.stopScrolling();
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
            }
        },

        /**
         * Event handler for touch move.
         *
         * @private
<<<<<<< HEAD
         * @param e Event
         */
        touchMove : function(e) {
            for (var i = 0; i < e.changedTouches.length; i++) {
                t.handleMove(e.changedTouches[i].clientX, e.changedTouches[i].clientY);
=======
         * @param {Object} e Event
         */
        touchMove: function(e) {
            for (var i = 0; i < e.changedTouches.length; i++) {
                autoscroll.handleMove(e.changedTouches[i].clientX, e.changedTouches[i].clientY);
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
            }
        },

        /**
         * Event handler for mouse move.
         *
         * @private
<<<<<<< HEAD
         * @param e Event
         */
        mouseMove : function(e) {
            t.handleMove(e.clientX, e.clientY);
=======
         * @param {Object} e Event
         */
        mouseMove: function(e) {
            autoscroll.handleMove(e.clientX, e.clientY);
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
        },

        /**
         * Handles user moving.
         *
         * @private
<<<<<<< HEAD
         * @param clientX X
         * @param clientY Y
         */
        handleMove : function(clientX, clientY) {
            // If near the bottom or top, start auto-scrolling.
            if (clientY < t.SCROLL_THRESHOLD) {
                t.scrollAmount = -Math.min(t.SCROLL_THRESHOLD - clientY, t.SCROLL_THRESHOLD);
            } else if (clientY > $(window).height() - t.SCROLL_THRESHOLD) {
                t.scrollAmount = Math.min(clientY - ($(window).height() - t.SCROLL_THRESHOLD), t.SCROLL_THRESHOLD);
            } else {
                t.scrollAmount = 0;
            }
            if (t.scrollAmount && t.scrollingId === null) {
                t.startScrolling();
            } else if (!t.scrollAmount && t.scrollingId !== null) {
                t.stopScrolling();
=======
         * @param {number} clientX X
         * @param {number} clientY Y
         */
        handleMove: function(clientX, clientY) {
            // If near the bottom or top, start auto-scrolling.
            if (clientY < autoscroll.SCROLL_THRESHOLD) {
                autoscroll.scrollAmount = -Math.min(autoscroll.SCROLL_THRESHOLD - clientY, autoscroll.SCROLL_THRESHOLD);
            } else if (clientY > $(window).height() - autoscroll.SCROLL_THRESHOLD) {
                autoscroll.scrollAmount = Math.min(clientY - ($(window).height() - autoscroll.SCROLL_THRESHOLD),
                    autoscroll.SCROLL_THRESHOLD);
            } else {
                autoscroll.scrollAmount = 0;
            }
            if (autoscroll.scrollAmount && autoscroll.scrollingId === null) {
                autoscroll.startScrolling();
            } else if (!autoscroll.scrollAmount && autoscroll.scrollingId !== null) {
                autoscroll.stopScrolling();
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
            }
        },

        /**
         * Starts automatic scrolling.
         *
         * @private
         */
<<<<<<< HEAD
        startScrolling : function() {
            var maxScroll = $(document).height - $(window).height;
            t.scrollingId = window.setInterval(function() {
                // Work out how much to scroll.
                var y = $(window).scrollTop();
                var offset = Math.round(t.scrollAmount * t.SCROLL_SPEED);
=======
        startScrolling: function() {
            var maxScroll = $(document).height() - $(window).height();
            autoscroll.scrollingId = window.setInterval(function() {
                // Work out how much to scroll.
                var y = $(window).scrollTop();
                var offset = Math.round(autoscroll.scrollAmount * autoscroll.SCROLL_SPEED);
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
                if (y + offset < 0) {
                    offset = -y;
                }
                if (y + offset > maxScroll) {
                    offset = maxScroll - y;
                }
                if (offset === 0) {
                    return;
                }

                // Scroll.
                $(window).scrollTop(y + offset);
                var realOffset = $(window).scrollTop() - y;
                if (realOffset === 0) {
                    return;
                }

                // Inform callback
<<<<<<< HEAD
                if (t.callback) {
                    t.callback(realOffset);
                }

            }, t.SCROLL_FREQUENCY);
=======
                if (autoscroll.callback) {
                    autoscroll.callback(realOffset);
                }

            }, autoscroll.SCROLL_FREQUENCY);
>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
        },

        /**
         * Stops the automatic scrolling.
         *
         * @private
         */
<<<<<<< HEAD
        stopScrolling : function() {
            window.clearInterval(t.scrollingId);
            t.scrollingId = null;
        }
    };
    return t;
=======
        stopScrolling: function() {
            window.clearInterval(autoscroll.scrollingId);
            autoscroll.scrollingId = null;
        }
    };

    return {
        /**
         * Starts automatic scrolling if user moves near edge of window.
         * This should be called in response to mouse down or touch start.
         *
         * @public
         * @param {Function} callback Optional callback that is called every time it scrolls
         */
        start: autoscroll.start,

        /**
         * Stops automatic scrolling. This should be called in response to mouse up or touch end.
         *
         * @public
         */
        stop: autoscroll.stop,
    };

>>>>>>> 3cced42eb37a1e92a2ff38feb1c099ec54cc4e20
});
