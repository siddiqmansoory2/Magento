<?php
return [
    'backend' => [
        'frontName' => 'admin_h4kqpc'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'fbd5a8210dfb6351d937981941108642'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'new-production-db.chlba2hpzhbs.ap-south-1.rds.amazonaws.com',
                'dbname' => 'papabrands_live_v2',
                'username' => 'admin',
                'password' => 'Sw2#EdfR4!89',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '69d_'
            ],
            'page_cache' => [
                'id_prefix' => '69d_'
            ]
        ],
        'allow_parallel_generation' => false
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'cache_import_product' => 1,
        'vertex' => 1
    ],
    'install' => [
        'date' => 'Sat, 21 Aug 2021 08:47:36 +0000'
    ],
    'dev' => [
        'debug' => [
            'debug_logging' => 0
        ]
    ],
    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 2000,
        'consumers' => [
            'product_action_attribute.update',
            'product_action_attribute.website.update',
            'exportProcessor',
            'codegeneratorProcessor'
        ]
    ]
];
