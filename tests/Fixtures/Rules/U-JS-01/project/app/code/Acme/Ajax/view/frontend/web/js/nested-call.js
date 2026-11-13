define(['jquery'], function ($) {
    'use strict';

    $.ajax({
        url: '/a',
        success: function () {
            $.get('/b').complete(function () {});
        }
    }).done(function () {});
});
