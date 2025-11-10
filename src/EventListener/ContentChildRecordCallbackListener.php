<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ContentModel;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class ContentChildRecordCallbackListener
{
    public function __construct(private readonly IncludesAggregator $aggregator)
    {
    }

    public function onChildRecordCallback(array $row): string
    {
        // Render child record
        $childRecord = (new \tl_content())->addCteType($row);

        // Get the content element
        $element = ContentModel::findById((int) $row['id']);

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
