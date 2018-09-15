<?php

return [
    '__name' => 'lib-upload',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/lib-upload.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-upload' => ['install','update','remove'],
        'etc/locale/en-US/form/error/upload.php' => ['install','update','remove'],
        'etc/locale/id-ID/form/error/upload.php' => ['install','update','remove'],
        'media' => ['install']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-model' => NULL
            ],
            [
                'lib-user' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'api' => NULL
            ]
        ],
        'optional' => [
            [
                'lib-media' => NULL
            ]
        ]
    ],
    '__gitignore' => [
        'media/*' => TRUE,
        '!media/.gitkeep' => TRUE
    ],
    'autoload' => [
        'classes' => [
            'LibUpload\\Controller' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/controller'
            ],
            'LibUpload\\Iface' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/interface'
            ],
            'LibUpload\\Keeper' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/keeper'
            ],
            'LibUpload\\Model' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/model'
            ],
            'LibUpload\\Validator' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/validator'
            ]
        ],
        'files' => []
    ],
    'libForm' => [
        'forms' => [
            'lib-upload' => [
                'file' => [
                    'label' => 'File',
                    'type' => 'file',
                    'rules' => [
                        'required' => TRUE,
                        'array' => 'assoc',
                        'upload-file' => TRUE
                    ]
                ],
                'form' => [
                    'label' => 'File Form',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'upload-form' => TRUE
                    ]
                ]
            ]
        ]
    ],
    'libValidator' => [
        'validators' => [
            'upload'      => 'LibUpload\\Validator\\Upload::upload',
            'upload-file' => 'LibUpload\\Validator\\Upload::file',
            'upload-form' => 'LibUpload\\Validator\\Upload::form'
        ],
        'errors' => [
            '15.0' => 'form.error.upload.invalid_form_name',
            '16.0.1' => 'form.error.upload.file_size_too_small',
            '16.0.2' => 'form.error.upload.file_size_too_big',
            '16.1' => 'form.error.upload.mime_type_not_acceptable',
            '16.2' => 'form.error.upload.file_extension_not_acceptable',
            '16.3.1' => 'form.error.upload.image_width_too_small',
            '16.3.2' => 'form.error.upload.image_width_too_big',
            '16.4.1' => 'form.error.upload.image_height_too_small',
            '16.4.2' => 'form.error.upload.image_height_too_big',
            '17.0' => 'form.error.upload.target_file_not_found',
            '17.1' => 'form.error.upload.target_file_not_accepted'
        ]
    ],
    'routes' => [
        'api' => [
            'apiUpload' => [
                'path' => [
                    'value' => '/upload'
                ],
                'method' => 'POST',
                'handler' => 'LibUpload\\Controller\\Upload::init'
            ]
        ]
    ],
    'libUpload' => [
        'base' => [
            'local' => 'media'
        ],
        'forms' => [],
        'keeper' => [
            'handler' => 'local',
            'handlers' => [
                'local' => [
                    'class' => 'LibUpload\\Keeper\\Local',
                    'use' => TRUE
                ]
            ]
        ]
    ]
];