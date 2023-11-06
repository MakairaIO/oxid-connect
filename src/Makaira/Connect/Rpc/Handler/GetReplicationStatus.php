<?php

namespace Makaira\Connect\Rpc\Handler;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\Connect\Entity\RevisionRepository;
use Makaira\Connect\Rpc\HandlerInterface;

class GetReplicationStatus implements HandlerInterface
{
    private $repository;

    /**
     * @param RevisionRepository $repository
     */
    public function __construct(RevisionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $request
     *
     * @return array
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function handle(array $request): array
    {
        $indices = $request['indices'] ?? [];

        foreach ($indices as &$index) {
            $index['openChanges'] = $this->repository->countChanges($index['lastRevision']);
        }
        unset($index);

        return $indices;
    }
}
