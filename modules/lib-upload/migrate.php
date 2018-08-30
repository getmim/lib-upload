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
                ]
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 42,
                'attrs' => [
                    'unique' => true,
                    'null' => false 
                ]
            ],
            'original' => [
                'type' => 'VARCHAR',
                'length' => 300,
                'attrs' => [
                    'null' => false 
                ]
            ],
            'mime' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false 
                ]
            ],
            'user' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'null' => false 
                ]
            ],
            'path' => [
                'type' => 'VARCHAR',
                'length' => 250,
                'attrs' => [
                    'null' => false
                ]
            ],
            'identity' => [
                'type' => 'VARCHAR',
                'length' => 50,
                'attrs' => [
                    'null' => false,
                    'unique' => true
                ]
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ]
            ]
        ],
        'indexes' => [
            'by_mime_original' => [
                'fields' => [
                    'mime' => [],
                    'original' => []
                ]
            ]
        ]
    ]
];