var config = {
    map: {
        '*': {
            'jquery/ui': 'jquery/compat'
        }
    },
    shim: {
        'Acme_Rules/js/library': {
            deps: ['jquery', "jquery/ui"]
        }
    },
    deps: [
        'Acme_Rules/js/everywhere',
        'Magento_Ui/js/core/app'
    ]
};
