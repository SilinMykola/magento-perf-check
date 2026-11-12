var config = {
    map: {
        '*': {
            deps: 'js/not-a-dependency'
        }
    },
    config: {
        deps: ['js/not-top-level']
    },
    'deps': ['js/theme']
};
