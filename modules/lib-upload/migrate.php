<?php

return [
    'LibUpload\\Model\\Media' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'primary_key' => true,
                    'auto_increment' => true
                ],
                'index' => 1000
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 42,
                'attrs' => [
                    'unique' => true,
                    'null' => false 
                ],
                'index' => 2000
            ],
            'original' => [
                'type' => 'VARCHAR',
                'length' => 255,
                'attrs' => [
                    'null' => false
                ],
                'index' => 3000
            ],
            'mime' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false 
                ],
                'index' => 4000
            ],
            'user' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'null' => false 
                ],
                'index' => 5000
            ],
            'path' => [
                'type' => 'VARCHAR',
                'length' => 191,
                'attrs' => [
                    'null' => false,
                    'unique' => true
                ],
                'index' => 6000
            ],
            'identity' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false,
                    'unique' => true
                ],
                'index' => 7000
            ],
            'size' => [
                'type' => 'INTEGER',
                'attrs' => [
                    'null' => false,
                    'unsigned' => true 
                ],
                'index' => 8000
            ],
            'form' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false
                ],
                'index' => 9000
            ],
            'width' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true
                ],
                'index' => 10000
            ],
            'height' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true
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
    ]
];