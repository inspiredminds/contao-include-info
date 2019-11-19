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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;

class OnloadCallbackListener
{
    private $paletteManipulator;

    public function __construct()
    {
        $this->paletteManipulator =
            PaletteManipulator::create()
                ->addLegend('includeinfo_legend', [], PaletteManipulator::POSITION_APPEND, true)
                ->addField('includeInfo', 'includeinfo_legend')
        ;
    }

    public function onLoadCallback(DataContainer $dc): void
    {
        foreach ($GLOBALS['TL_DCA'][$dc->table]['palettes'] as $name => $palette) {
            if (\is_string($palette)) {
                $this->paletteManipulator->applyToPalette($name, $dc->table);
            }
        }
    }
}
