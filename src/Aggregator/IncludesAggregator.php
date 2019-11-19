<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\Aggregator;

class IncludesAggregator
{
    public function getContentElementIncludes(int $contentElementId): array
    {
        return [];
    }

    public function getArticleIncludes(int $articleId): array
    {
        return [];
    }
}
