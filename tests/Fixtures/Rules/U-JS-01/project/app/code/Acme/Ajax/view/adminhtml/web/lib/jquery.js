/*! A copy of jQuery 1.x, as a module ships it: its own jqXHR still has complete(). */
(function (window) {
    var jQuery = function () {};
    jQuery.fn = jQuery.prototype = {};
    jQuery.fn.load = function (url, callback) {
        jQuery.ajax({url: url}).complete(callback);
    };
})(window);
