<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;

/**
 * Removes the `tl_inserttag_index` DCA if index is not enabled.
 */
#[AsHook('loadDataContainer')]
class DisableInsertTagIndexListener
{
    public function __construct(private readonly bool $enableIndex)
    {
    }

    public function __invoke(string $table): void
    {
        if ($this->enableIndex || 'tl_inserttag_index' !== $table) {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_inserttag_index']);
    }
}
