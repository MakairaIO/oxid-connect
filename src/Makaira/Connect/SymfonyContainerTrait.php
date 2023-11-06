<?php

namespace Makaira\Connect;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\ContainerInterface;

trait SymfonyContainerTrait
{
    public function getSymfonyContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
