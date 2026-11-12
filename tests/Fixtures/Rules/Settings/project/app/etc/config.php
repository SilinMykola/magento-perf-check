<?php
return [
    'modules' => [
        'Magento_Store' => 1,
    ],
    'dev' => [
        'js' => [
            // Outside "system": not a setting Magento reads.
            'merge_files' => '1',
        ],
    ],
    'admin' => [
        'default' => [
            'dev' => [
                'js' => [
                    // Not under "system" either.
                    'merge_files' => '1',
                ],
            ],
        ],
    ],
    'system' => [
        'default' => [
            'dev' => [
                'js' => [
                    'merge_files' => '1',
                    'minify_files' => '1',
                ],
                'css' => [
                    'merge_css_files' => '1',
                ],
            ],
            'web' => [
                'js' => [
                    'merge_files' => '1',
                ],
                'dev' => [
                    'js' => [
                        'merge_files' => '1',
                    ],
                ],
            ],
        ],
        'websites' => [
            'base' => [
                'dev' => [
                    'js' => [
                        'merge_files' => '0',
                    ],
                ],
                'web' => [
                    'dev' => [
                        'js' => [
                            'merge_files' => '1',
                        ],
                    ],
                ],
            ],
            'outlet' => [
                'dev' => [
                    'js' => [
                        'merge_files' => true,
                    ],
                ],
            ],
        ],
        'stores' => [
            'en' => [
                'dev' => [
                    'js' => [
                        'merge_files' => 1,
                    ],
                ],
            ],
            'de' => [
                'dev' => [
                    'js' => [
                        'merge_files' => '',
                    ],
                ],
            ],
            'fr' => [
                'web' => [
                    'dev' => [
                        'js' => [
                            'merge_files' => '1',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
