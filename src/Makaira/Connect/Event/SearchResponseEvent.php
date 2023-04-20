<?php

declare(strict_types=1);

namespace Makaira\Connect\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * After receiving the $productIds you can still adjust them
 */
class SearchResponseEvent extends Event
{
    public const NAME = 'makaira.response.search';

    /**
     * @var array
     */
    private $productIds;

    public function __construct(array $productIds)
    {
        $this->productIds = new \ArrayObject($productIds);
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->productIds;
    }
}
