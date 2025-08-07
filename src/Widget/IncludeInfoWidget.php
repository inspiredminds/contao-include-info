<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

namespace InspiredMinds\IncludeInfoBundle\Widget;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\FormModel;
use Contao\ModuleModel;
use Contao\System;
use Contao\Widget;
use InspiredMinds\IncludeInfoBundle\Aggregator\IncludesAggregator;

class IncludeInfoWidget extends Widget
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
            $element = ContentModel::findById((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForContentElement($element);
        } elseif (ArticleModel::getTable() === $table) {
            $article = ArticleModel::findById((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForArticle($article);
        } elseif (ModuleModel::getTable() === $table) {
            $module = ModuleModel::findById((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForModule($module);
        } elseif (FormModel::getTable() === $table) {
            $form = FormModel::findById((int) $activeRecord->id);

            $includes = $aggregator->renderIncludesForForm($form);
        } else {
            return '<p class="tl_error">Widget not supported for '.$table.'</p>';
        }

        return $includes ?? '<p class="tl_help">'.$GLOBALS['TL_LANG']['MSC']['inc_noIncludes'].'</p>';
    }
}
