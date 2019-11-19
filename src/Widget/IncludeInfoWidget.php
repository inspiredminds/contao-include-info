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

    public function generate()
    {
        // Get the active record
        $activeRecord = $this->dataContainer->activeRecord;

        // Get the type
        $type = $activeRecord->type;

        // Get the table
        $table = $this->strTable;

        // Get the aggregator service
        $aggregator = System::getContainer()->get(IncludesAggregator::class);

        // Aggregate the includes
        $includes = [];

        // Determine aggregation type
        if ('alias' === $type || 'tl_content' === $table) {
            $includes = $aggregator->getContentElementIncludes((int) $activeRecord->cteAlias);
        } elseif ('article' === $type || 'tl_article' === $table) {
            $includes = $aggregator->getArticleIncludes((int) $activeRecord->articleAlias);
        }

        return $this->parse(['includes' => $includes]);
    }
}
