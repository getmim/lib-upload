<?php

return [
    'LibUpload\\Model\\Media' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'primary_key' => TRUE,
                    'auto_increment' => TRUE
                ],
                'index' => 1000
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 42,
                'attrs' => [
                    'unique' => TRUE,
                    'null' => FALSE
                ],
                'index' => 2000
            ],
            'original' => [
                'type' => 'VARCHAR',
                'length' => 255,
                'attrs' => [
                    'null' => FALSE
                ],
                'index' => 3000
            ],
            'mime' => [
                'type' => 'VARCHAR',
                'length' => 100,
                'attrs' => [
                    'null' => FALSE
                ],
                'index' => 4000
            ],
            'user' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'index' => 5000
            ],
            'path' => [
                'type' => 'VARCHAR',
                'length' => 191,
                'attrs' => [
                    'null' => FALSE,
                    'unique' => TRUE
                ],
                'index' => 6000
            ],
            'identity' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => FALSE,
                    'unique' => TRUE
                ],
                'index' => 7000
            ],
            'size' => [
                'type' => 'INTEGER',
                'attrs' => [
                    'null' => FALSE,
                    'unsigned' => TRUE
                ],
                'index' => 8000
            ],
            'form' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => FALSE
                ],
                'index' => 9000
            ],
            'width' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE
                ],
                'index' => 10000
            ],
            'height' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE
                ],
                'index' => 11000
            ],
            'urls' => [
                'type' => 'TEXT',
                'attrs' => [],
                'index' => 12000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 13000
            ]
        ],
        'indexes' => [
            'by_mime_original' => [
                'fields' => [
                    'mime' => [],
                    'original' => [
                        'length' => 191
                    ]
                ]
            ]
        ]
    ],
    'LibUpload\\Model\\MediaAuth' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'primary_key' => TRUE,
                    'auto_increment' => TRUE
                ],
                'index' => 1000
            ],
            'type' => [
                'type' => 'VARCHAR',
                'attrs' => [
                    'null' => false 
                ],
                'index' => 2000
            ],
            'object' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => TRUE,
                    'null' => FALSE
                ],
                'index' => 3000
            ],
            'media' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'null' => false 
                ],
                'index' => 4000
            ],
            'updated' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP',
                    'update' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 10000
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 11000
            ]
        ]
    ]
];