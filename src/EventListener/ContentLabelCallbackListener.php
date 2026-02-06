<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ContentModel;
use Contao\DataContainer;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

readonly class ContentLabelCallbackListener
{
    public function __construct(private IncludesAggregator $aggregator)
    {
    }

    public function onLabelCallback(array $row): array
    {
        // Render label
        $label = (new \tl_content())->addCteType($row);

        // Get the content element
        $element = ContentModel::findById((int) $row['id']);

        // Render the include info for the content element
        $includeInfo = $this->aggregator->renderIncludesForContentElement($element);

        dump($includeInfo);

        if ($includeInfo) {
            $label[1] = $includeInfo . $label[1] ?? '';
        }

        dump($label);

        return $label;
    }
}
