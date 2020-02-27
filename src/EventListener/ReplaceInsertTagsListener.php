<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

class ReplaceInsertTagsListener
{
    public const INDEX_INSERT_TAG = 'include_info_index';

    private $db;
    private $requestStack;
    private $insertTags = [];

    public function __invoke(
        string $insertTag,
        bool $useCache,
        string $cachedValue,
        array $flags,
        array $tags,
        array $cache
    ) {
        if (self::INDEX_INSERT_TAG === $insertTag) {
            $this->insertTags = array_keys($cache);

            return '';
        }

        return false;
    }

    public function getInsertTags(): array
    {
        return $this->insertTags;
    }
}
