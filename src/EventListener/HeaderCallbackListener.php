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
        if ('article' === Input::get('do')) {
            $article = ArticleModel::findByPk((int) Input::get('id'));
            $includeInfo = $this->aggregator->renderIncludesForArticle($article);

            if (!empty($includeInfo)) {
                $add[$GLOBALS['TL_LANG']['tl_article']['includeinfo_legend']] = $includeInfo;
            }
        } elseif ('form' === Input::get('do')) {
            $form = FormModel::findByPk((int) Input::get('id'));
            $includeInfo = $this->aggregator->renderIncludesForForm($form);

            if (!empty($includeInfo)) {
                $add[$GLOBALS['TL_LANG']['tl_form']['includeinfo_legend']] = $includeInfo;
            }
        }

        return $add;
    }
}
