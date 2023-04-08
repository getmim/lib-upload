<?php

return [
    '__name' => 'lib-upload',
    '__version' => '0.9.0',
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
            ],
            [
                'lib-formatter' => NULL
            ]
        ]
    ],
    '__gitignore' => [
        'media/*' => TRUE,
        '!media/.gitkeep' => TRUE
    ],
    '__inject' => [
        [
            'name' => 'libUpload',
            'children' => [
                [
                    'name' => 'base',
                    'question' => 'Would you like to configure local storage media?',
                    'default' => TRUE,
                    'rule' => 'boolean',
                    'injector' => [
                        'class' => 'LibUpload\\Library\\Cli',
                        'method' => 'local'
                    ]
                ]
            ]
        ]
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
            ],
            'LibUpload\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-upload/library'
            ]
        ],
        'files' => []
    ],
    'libForm' => [
        'forms' => [
            'std-cover' => [
                'cover-url' => [
                    'label' => 'Cover',
                    'type' => 'image',
                    'form' => 'std-image',
                    'rules' => [
                        'required' => TRUE,
                        'upload' => TRUE
                    ]
                ],
                'cover-label' => [
                    'label' => 'Cover Label',
                    'type' => 'text',
                    'rules' => []
                ]
            ],
            'lib-upload' => [
                'file' => [
                    'label' => 'File',
                    'type' => 'file',
                    'rules' => [
                        'required' => TRUE,
                        'array' => 'assoc',
                        'upload-file' => TRUE
                    ],
                    'filters' => [
                        'array' => TRUE
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
            ],
            'lib-upload-validate' => [
                'file' => [
                    'label' => 'File',
                    'type' => 'file',
                    'rules' => [
                        'required' => TRUE,
                        'upload-mock' => TRUE
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
            ],
            'lib-upload-chunk' => [
                'file' => [
                    'label' => 'File',
                    'type' => 'file',
                    'rules' => [
                        'required' => TRUE,
                        'file' => TRUE
                    ]
                ],
                'form' => [
                    'label' => 'File Form',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'upload-form' => TRUE
                    ]
                ],
                'token' => [
                    'label' => 'Token',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ]
            ],
            'lib-upload-finalize' => [
                'form' => [
                    'label' => 'File Form',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'upload-form' => TRUE
                    ]
                ],
                'token' => [
                    'label' => 'Token',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ],
                'name' => [
                    'label' => 'Original File Name',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE
                    ]
                ]
            ]
        ]
    ],
    'libValidator' => [
        'validators' => [
            'upload' => 'LibUpload\\Validator\\Upload::upload',
            'upload-file' => 'LibUpload\\Validator\\Upload::file',
            'upload-form' => 'LibUpload\\Validator\\Upload::form',
            'upload-list' => 'LibUpload\\Validator\\Upload::uploadList',
            'upload-mock' => 'LibUpload\\Validator\\Upload::mock'
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
            '17.1' => 'form.error.upload.target_file_not_acceptable',
            '18.0' => 'form.error.upload.one_or_more_target_file_not_found',
            '18.1' => 'form.error.upload.one_or_more_target_file_not_acceptable',
            '18.2' => 'form.error.upload.invalid_object_request_format',
            '27.0' => 'form.error.upload.php_upload_err_unknown',
            '27.1' => 'form.error.upload.php_upload_err_ini_size',
            '27.2' => 'form.error.upload.php_upload_err_form_size',
            '27.3' => 'form.error.upload.php_upload_err_partial',
            '27.4' => 'form.error.upload.php_upload_err_no_file',
            '27.5' => 'form.error.upload.php_upload_err_no_tmp_dir',
            '27.6' => 'form.error.upload.php_upload_err_cant_write',
            '27.7' => 'form.error.upload.php_upload_err_extension'
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
            ],
            'apiUploadFilter' => [
                'path' => [
                    'value' => '/upload/filter'
                ],
                'method' => 'GET',
                'handler' => 'LibUpload\\Controller\\Upload::filter'
            ],
            'apiUploadValidate' => [
                'path' => [
                    'value' => '/upload/validate'
                ],
                'method' => 'POST',
                'handler' => 'LibUpload\\Controller\\Upload::validate'
            ],
            'apiUploadChunk' => [
                'path' => [
                    'value' => '/upload/chunk'
                ],
                'method' => 'POST',
                'handler' => 'LibUpload\\Controller\\Upload::chunk'
            ],
            'apiUploadFinalize' => [
                'path' => [
                    'value' => '/upload/finalize'
                ],
                'method' => 'POST',
                'handler' => 'LibUpload\\Controller\\Upload::finalize'
            ]
        ]
    ],
    'libUpload' => [
        'authorizer' => [],
        'filter' => [
            'own' => FALSE
        ],
        'base' => [
            'local' => 'media',
            'host' => '/media/'
        ],
        'forms' => [
            'std-image' => [
                'mime' => ['image/*']
            ],
            'std-audio' => [
                'mime' => ['audio/*']
            ],
            'std-video' => [
                'mime' => ['video/*']
            ]
        ],
        'keeper' => [
            'handler' => 'local',
            'handlers' => [
                'local' => [
                    'class' => 'LibUpload\\Keeper\\Local',
                    'use' => TRUE
                ]
            ]
        ]
    ],
    'libFormatter' => [
        'formats' => [
            'std-cover' => [
                'url' => [
                    'type' => 'media'
                ],
                'label' => [
                    'type' => 'text'
                ]
            ]
        ],
        'handlers' => [
            'std-cover' => [
                'handler' => 'LibUpload\\Library\\Format::stdCover',
                'collective' => TRUE
            ]
        ]
    ]
];
