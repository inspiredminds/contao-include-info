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

use Contao\ContentModel;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;
use tl_content;

class ContentChildRecordCallbackListener
{
    private $aggregator;

    public function __construct(IncludesAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function onChildRecordCallback(array $row): string
    {
        // Render child record
        $childRecord = (new tl_content())->addCteType($row);

        if ('tl_article' !== $row['ptable']) {
            return $childRecord;
        }

        // Get the content element
        $element = ContentModel::findByPk((int) $row['id']);

        // Render the include info for the content element
        $includeInfo = $this->aggregator->renderIncludesForContentElement($element);

        // Inject include info into child record
        $pos = strpos($childRecord, '</div>');
        if (false !== $pos && null !== $includeInfo) {
            $childRecord = substr_replace($childRecord, $includeInfo, $pos + 6, 0);
        }

        return $childRecord;
    }
}
