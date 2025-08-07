<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook("replaceInsertTags")
 */
class ReplaceInsertTagsListener
{
    public const INDEX_INSERT_TAG = 'include_info_index';

    private $insertTags = [];

    public function __invoke(string $insertTag, bool $useCache, string $cachedValue, array $flags, array $tags, array $cache)
    {
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
