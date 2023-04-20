<?php

declare(strict_types=1);

namespace Makaira\Connect\Event;

use Makaira\Query;
use Symfony\Component\EventDispatcher\Event;

/**
 * Possibility to change the query before sending it to Makaira.
 */
class ModifierQueryRequestEvent extends Event
{
    public const NAME_SEARCH = 'makaira.request.modifier.query.search';

    public const NAME_AUTOSUGGESTER = 'makaira.request.modifier.query.autosuggester';

    private $query;

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
