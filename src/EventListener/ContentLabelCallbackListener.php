<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\EventListener\DataContainer\ContentElementViewListener;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentLabelCallbackListener extends ContentElementViewListener
{
    public function __construct(
        private readonly IncludesAggregator $aggregator,
        private readonly ContentElementViewListener $inner,
        ContaoFramework $framework,
        TranslatorInterface $translator,
    ) {
        parent::__construct($framework, $translator);
    }

    public function generateLabel(array $row, string $label, DataContainer $dc): array|string
    {
        // Render label
        $label = $this->inner->generateLabel($row, $label, $dc);

        // Get the content element
        $element = ContentModel::findById((int) $row['id']);

        // Render the include info for the content element
        $includeInfo = $this->aggregator->renderIncludesForContentElement($element);

        if ($includeInfo) {
            if (\is_array($label)) {
                $label[1] = $includeInfo.($label[1] ?? '');
            } else {
                $label = $includeInfo.$label;
            }
        }

        return $label;
    }
}
