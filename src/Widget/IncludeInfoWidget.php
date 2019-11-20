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

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\System;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class IncludeInfoWidget extends \Contao\Widget
{
    public function generate(): void
    {
    }

    public function parse($attributes = null): string
    {
        // Get the active record
        $activeRecord = $this->dataContainer->activeRecord;

        // Get the table
        $table = $this->strTable;

        // Get the aggregator service
        /** @var IncludesAggregator $aggregator */
        $aggregator = System::getContainer()->get(IncludesAggregator::class);

        // Render includes
        $includes = null;

        if (ContentModel::getTable() === $table) {
            $element = ContentModel::findByPk((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForContentElement($element);
        } elseif (ArticleModel::getTable() === $table) {
            $article = ArticleModel::findByPk((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForArticle($article);
        } elseif (ModuleModel::getTable() === $table) {
            $module = ModuleModel::findByPk((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForModule($module);
        } elseif (FormModel::getTable() === $table) {
            $form = FormModel::findByPk((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForForm($form);
        } else {
            return '<p class="tl_error">Widget not supported for '.$table.'</p>';
        }

        return $includes ?? '<p class="tl_help">'.$GLOBALS['TL_LANG']['MSC']['inc_noIncludes'].'</p>';
    }
}
