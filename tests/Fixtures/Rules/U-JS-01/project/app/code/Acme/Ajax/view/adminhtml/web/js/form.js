require(['jquery'], function ($) {
    $.post('/admin/save', {}).success(function (data) {
        console.log(data);
    });
});
