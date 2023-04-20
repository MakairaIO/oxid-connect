<?php

declare(strict_types=1);

namespace Makaira\Connect\Event;

use Makaira\Result;
use Symfony\Component\EventDispatcher\Event;

/**
 * After receiving the Auggester, you can still adjust it for the display.
 */
class AutoSuggesterResponseEvent extends Event
{
    public const NAME = 'makaira.response.autosuggester';

    private $result;

    public function __construct($result)
    {
        $this->result = new \ArrayObject($result);
    }

    /**
     * @return \ArrayObject
     */
    public function getResult()
    {
        return $this->result;
    }
}
