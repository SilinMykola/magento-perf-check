define(['jquery', 'mage/storage'], function ($, storage) {
    'use strict';

    return function (url, payload) {
        storage.post(
            url,
            payload,
            true
        ).success(
            function (response) {
                return response;
            }
        ).error(function () {});
        jQuery.getJSON(url).then(function () {}).fail(function () {});
        $.get(url).always(function () {});
        $.getScript(url).complete(function () {});
    };
});
