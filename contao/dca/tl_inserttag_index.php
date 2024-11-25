<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

use Doctrine\DBAL\Platforms\MySQLPlatform;

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

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
