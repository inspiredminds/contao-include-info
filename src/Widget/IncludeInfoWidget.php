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
        if (ContentModel::getTable() === $table) {
            $element = ContentModel::findByPk((int) $activeRecord->id);

            return $aggregator->renderIncludesForContentElement($element) ?? '';
        }

        if (ArticleModel::getTable() === $table) {
            $article = ArticleModel::findByPk((int) $activeRecord->id);

            return $aggregator->renderIncludesForArticle($article) ?? '';
        }

        if (ModuleModel::getTable() === $table) {
            $module = ModuleModel::findByPk((int) $activeRecord->id);

            return $aggregator->renderIncludesForModule($module) ?? '';
        }

        if (FormModel::getTable() === $table) {
            $form = FormModel::findByPk((int) $activeRecord->id);

            return $aggregator->renderIncludesForForm($form) ?? '';
        }

        return '<p class="tl_error">Widget not supported for '.$table.'</p>';
    }
}
