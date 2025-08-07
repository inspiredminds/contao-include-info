<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\ArticleModel;
use Contao\DataContainer;
use Contao\FormModel;
use Contao\Input;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class HeaderCallbackListener
{
    public function __construct(private readonly IncludesAggregator $aggregator)
    {
    }

    public function onHeaderCallback(array $add, DataContainer $dc): array
    {
        if (null !== Input::get('act')) {
            return $add;
        }

        if ('tl_content' === $dc->table) {
            if (null !== ($article = ArticleModel::findById((int) $dc->id))) {
                $includeInfo = $this->aggregator->renderIncludesForArticle($article);

                if (null !== $includeInfo && '' !== $includeInfo && '0' !== $includeInfo) {
                    $add[$GLOBALS['TL_LANG']['tl_article']['includeinfo_legend']] = $includeInfo;
                }
            }
        } elseif ('tl_form_field' === $dc->table) {
            if (null !== ($form = FormModel::findById((int) $dc->id))) {
                $includeInfo = $this->aggregator->renderIncludesForForm($form);

                if (null !== $includeInfo && '' !== $includeInfo && '0' !== $includeInfo) {
                    $add[$GLOBALS['TL_LANG']['tl_form']['includeinfo_legend']] = $includeInfo;
                }
            }
        }

        return $add;
    }
}
