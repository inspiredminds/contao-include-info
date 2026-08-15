<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\DataContainer\RecordLabel;
use Contao\CoreBundle\EventListener\DataContainer\ContentElementViewListener;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

if (class_exists(RecordLabel::class)) {
    class ContentLabelCallbackParent extends ContentElementViewListener
    {
        public function generateLabel(array $row, string $label, DataContainer $dc): RecordLabel
        {
            return $this->doGenerateLabel($row, $label, $dc);
        }
    }
} else {
    class ContentLabelCallbackParent extends ContentElementViewListener
    {
        public function generateLabel(array $row, string $label, DataContainer $dc): array|string
        {
            return $this->doGenerateLabel($row, $label, $dc);
        }
    }
}

class ContentLabelCallbackListener extends ContentLabelCallbackParent
{
    public function __construct(
        private readonly IncludesAggregator $aggregator,
        private readonly ContentElementViewListener $inner,
        private readonly Environment|null $twig,
        ContaoFramework $framework,
        TranslatorInterface $translator,
    ) {
        parent::__construct($framework, $translator, $twig);
    }

    protected function doGenerateLabel(array $row, string $label, DataContainer $dc): mixed
    {
        // Render label
        $label = $this->inner->generateLabel($row, $label, $dc);

        // Get the content element
        $element = ContentModel::findById((int) $row['id']);

        // Render the include info for the content element
        $includeInfo = $this->aggregator->renderIncludesForContentElement($element);

        if ($includeInfo) {
            if ($label instanceof RecordLabel) {
                $label->htmlPreview = $includeInfo.($label->htmlPreview ?? '');
            } elseif (\is_array($label)) {
                $label[1] = $includeInfo.($label[1] ?? '');
            } else {
                $label = $includeInfo.$label;
            }
        }

        return $label;
    }
}
