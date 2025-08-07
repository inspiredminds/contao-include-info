<?php

declare(strict_types=1);

/*
 * (c) INSPIRED MINDS
 */

use InspiredMinds\IncludeInfoBundle\EventListener\HeaderCallbackListener;

$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['header_callback'] = [HeaderCallbackListener::class, 'onHeaderCallback'];
