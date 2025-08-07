<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use Doctrine\DBAL\Platforms\MySQLPlatform;

$GLOBALS['TL_DCA']['tl_inserttag_index'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tag,params' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'notnull' => false, 'default' => null],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'url' => [
            'sql' => ['type' => 'text', 'length' => MySQLPlatform::LENGTH_LIMIT_TEXT, 'notnull' => false],
        ],
        'tag' => [
            'sql' => ['type' => 'string', 'length' => 200, 'default' => ''],
        ],
        'params' => [
            'sql' => ['type' => 'string', 'length' => 200, 'default' => ''],
        ],
        'flags' => [
            'sql' => ['type' => 'string', 'length' => 200, 'default' => ''],
        ],
    ],
];
