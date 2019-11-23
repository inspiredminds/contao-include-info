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

use Contao\ArticleModel;
use Contao\DataContainer;
use Contao\FormModel;
use Contao\Input;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class HeaderCallbackListener
{
    private $aggregator;

    public function __construct(IncludesAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function onHeaderCallback(array $add, DataContainer $dc): array
    {
        if (null !== Input::get('act')) {
            return $add;
        }

        if ('tl_content' === $dc->table) {
            if (null !== ($article = ArticleModel::findByPk((int) $dc->id))) {
                $includeInfo = $this->aggregator->renderIncludesForArticle($article);

                if (!empty($includeInfo)) {
                    $add[$GLOBALS['TL_LANG']['tl_article']['includeinfo_legend']] = $includeInfo;
                }
            }
        } elseif ('tl_form_field' === $dc->table) {
            if (null !== ($form = FormModel::findByPk((int) $dc->id))) {
                $includeInfo = $this->aggregator->renderIncludesForForm($form);

                if (!empty($includeInfo)) {
                    $add[$GLOBALS['TL_LANG']['tl_form']['includeinfo_legend']] = $includeInfo;
                }
            }
        }

        return $add;
    }
}
