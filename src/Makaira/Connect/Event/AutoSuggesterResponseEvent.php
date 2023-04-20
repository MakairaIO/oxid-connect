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

    public function __construct(Result &$result)
    {
        $this->result = $result;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
