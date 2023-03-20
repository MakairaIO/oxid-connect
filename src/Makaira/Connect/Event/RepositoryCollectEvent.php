<?php
/**
 * This file is part of a Makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Martin Schnabel <ms@marmalade.group>
 * Author URI: https://www.makaira.io/
 */

namespace Makaira\Connect\Event;

use Makaira\Connect\Repository;
use Makaira\Connect\Repository\AbstractRepository;
use Symfony\Contracts\EventDispatcher\Event;

class RepositoryCollectEvent extends Event
{

    /**
     * @var Repository
     */
    public $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function addRepository(AbstractRepository $repository)
    {
        $this->repository->addRepositoryMapping($repository);
    }
}
