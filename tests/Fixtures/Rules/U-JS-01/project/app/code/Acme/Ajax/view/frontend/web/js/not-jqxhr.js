define(['jquery'], function ($) {
    'use strict';

    return {
        reset: function () {
            this.city().clear().error(false);
            this.storage.get('key').success(function () {});
            $('.form').on('submit', function () {}).error(function () {});
            // $.ajax(url).success(function () {});
            var text = "$.ajax(url).success(fn)";
            $.get('/a').always(function () {}).promise().done(function () {}).complete(function () {});
            $.ajax('/b').then(function () {}).success(function () {});
            window.jQuery.ajax('/c').success(function () {});
            $j.ajax('/d').success(function () {});
            $.ajax('/e')?.success(function () {});
        }
    };
});
