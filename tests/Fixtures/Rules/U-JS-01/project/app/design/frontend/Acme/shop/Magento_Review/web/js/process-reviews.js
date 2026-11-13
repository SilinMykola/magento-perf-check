define(['jquery'], function ($) {
    'use strict';

    return function (url) {
        $.ajax({
            url: url,
            cache: true
        }).done(function (data) {
            $('#product-review-container').html(data);
        }).complete(function () {
            $('#product-review-container').trigger('contentUpdated');
        });
    };
});
