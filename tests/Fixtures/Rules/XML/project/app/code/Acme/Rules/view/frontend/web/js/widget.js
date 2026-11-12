// define(['jquery', 'jquery/ui'], function () {});
/* require(['jquery/ui']); */
define([
    'jquery',
    'jquery/ui',
    'jquery/ui/widget'
], function ($) {
    'use strict';

    var quoted = /"'/.test('x') ? 'jquery/ui' : '',
        message = `two
lines`,
        ratio = 4 / 2,
        escaped = 'jquery\/ui';

    // perf-ignore J-DEP-01: the slider needs the whole library here
    require(['jquery/ui']);

    return {map: {'jquery/ui': 'jquery/compat'}, quoted: quoted, message: message, ratio: ratio, escaped: escaped};
});
