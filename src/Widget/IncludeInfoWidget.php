<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\Widget;

use Contao\System;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class IncludeInfoWidget extends \Contao\Widget
{
    protected $strTemplate = 'be_include';

    public function generate(): void
    {
    }

    public function parse($attributes = null): string
    {
        // Get the active record
        $activeRecord = $this->dataContainer->activeRecord;

        // Get the type
        $type = $activeRecord->type;

        // Get the table
        $table = $this->strTable;

        // Get the aggregator service
        /** @var IncludesAggregator $aggregator */
        $aggregator = System::getContainer()->get(IncludesAggregator::class);

        // Determine aggregation
        if ('alias' === $type) {
            $this->original = $aggregator->getContentElement((int) $activeRecord->cteAlias);
            $includes = $aggregator->getContentElementIncludes((int) $activeRecord->cteAlias);
            unset($includes[(int) $activeRecord->id]);
            $this->includes = $includes;
        } elseif ('article' === $type) {
            $this->original = $aggregator->getArticle((int) $activeRecord->articleAlias);
            $includes = $aggregator->getArticleIncludes((int) $activeRecord->articleAlias);
            unset($includes[(int) $activeRecord->id]);
            $this->includes = $includes;
        } elseif ('tl_content' === $table) {
            $this->includes = $aggregator->getContentElementIncludes((int) $activeRecord->id);
        } elseif ('tl_article' === $table) {
            $this->includes = $aggregator->getArticleIncludes((int) $activeRecord->id);
        }

        // Add CSS
        $GLOBALS['TL_CSS'][self::class] = 'bundles/includeinfo/be_styles.css';

        if (empty($this->includes) && empty($this->original)) {
            return '';
        }

        return parent::parse($attributes);
    }
}
