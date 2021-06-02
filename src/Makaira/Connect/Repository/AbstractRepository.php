<?php

namespace Makaira\Connect\Repository;

use Makaira\Connect\Change;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Event\RepositoryCollectEvent;
use Makaira\Connect\Utils\TableTranslator;

abstract class AbstractRepository
{
    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @var ModifierList
     */
    protected $modifiers;

    /**
     * @var TableTranslator
     */
    protected $tableTranslator;

    public function __construct(DatabaseInterface $database, ModifierList $modifiers, TableTranslator $tableTranslator)
    {
        $this->database        = $database;
        $this->modifiers       = $modifiers;
        $this->tableTranslator = $tableTranslator;
    }

    public function addRepository($e)
    {
        if ($e instanceof RepositoryCollectEvent) {
            $e->addRepository($this);
        }
    }

    public function get($id)
    {
        $result = $this->database->query($this->getSelectQuery(), ['id' => $id]);

        $change       = new Change();
        $change->id   = $id;
        $change->type = $this->getType();

        if (empty($result)) {
            $change->deleted = true;

            return $change;
        }

        $type         = $this->getInstance($result[0]);
        $type         = $this->modifiers->applyModifiers($type, $this->getType());
        $change->data = $type;

        return $change;
    }

    /**
     * Get all IDs handled by this repository.
     *
     * @return string[]
     */
    public function getAllIds($shopId = null)
    {
        $sql = $this->getAllIdsQuery();
        $this->tableTranslator->setShopId($shopId);
        $sql = $this->tableTranslator->translate($sql);
        $result      = $this->database->query($sql);

        return array_map(
            static function ($row) {
                return $row['OXID'];
            },
            $result
        );
    }

    abstract public function getType();

    abstract protected function getInstance($id);

    abstract protected function getSelectQuery();

    abstract protected function getAllIdsQuery();

    abstract protected function getParentIdQuery();
}
