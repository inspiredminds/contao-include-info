<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_inserttag_index'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
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
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'tag' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'params' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'flags' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
    ],
];
