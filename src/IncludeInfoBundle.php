<?php

declare(strict_types=1);

/*
 * This file is part of the IncludeInfoBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\IncludeInfoBundle;

use InspiredMinds\IncludeInfoBundle\DependencyInjection\IncludeInfoExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IncludeInfoBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new IncludeInfoExtension();
        }

        return $this->extension;
    }
}
