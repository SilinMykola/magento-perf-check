define(['jquery'], function () {
    // perf-ignore J-DEP-01: the whole library is needed here
    require(['jquery/ui']);
    require(['jquery/ui']); /* perf-ignore J-DEP-01: same line */
    // perf-ignore J-DEP-01
    require(['jquery/ui']);
    // perf-ignore X-LAY-01: another rule
    require(['jquery/ui']);
    // perf-ignore J-DEP-01: not adjacent

    require(['jquery/ui']);
});
