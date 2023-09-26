<?php

namespace Makaira\Connect\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Utils\TableTranslator;
use PDO;
use PDOStatement;

/**
 * Simple database facade so we do not need to set fetch mode before each query
 *
 * @version $Revision$
 */
class DoctrineDatabase implements DatabaseInterface
{
    /**
     * @var Connection
     */
    private Connection $database;

    /**
     * @var Statement[]
     */
    private array $preparedStatements = [];

    /** @var  TableTranslator */
    private TableTranslator $translator;

    public function __construct(Connection $database, TableTranslator $translator)
    {
        $this->database   = $database;
        $this->translator = $translator;
    }

    /**
     * Execute query without return value
     * Will replace ':parameter' in the query with the quoted value from the
     * $parameters array.
     *
     * @param string $query
     * @param array  $parameters
     * @param bool   $translateTables
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function execute(string $query, array $parameters = array(), bool $translateTables = true): int
    {
        $query = $this->translatesTables($translateTables, $query);

        $statement = $this->prepareStatement($query);
        $this->bindQueryParameters($statement, $parameters);

        $statement->execute();

        return $statement->rowCount();
    }

    /**
     * Query database in ADODB_FETCH_ASSOC mode
     * Will replace ':parameter' in the query with the quoted value from the
     * $parameters array.
     *
     * @param string $query
     * @param array  $parameters
     * @param bool   $translateTables
     *
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function query(string $query, array $parameters = array(), bool $translateTables = true): array
    {
        $query     = $this->translatesTables($translateTables, $query);
        $statement = $this->prepareStatement($query);
        $this->bindQueryParameters($statement, $parameters);

        $result = $statement->fetchAllAssociative();
        $wrappedStatement = $statement->getWrappedStatement();
        if ($wrappedStatement instanceof PDOStatement) {
            foreach ($result as $nr => $row) {
                $column = 0;
                foreach ($row as $key => $field) {
                    $meta = $wrappedStatement->getColumnMeta($column++);

                    switch ($meta['native_type']) {
                        case 'TINY':
                        case 'LONG':
                        case 'LONGLONG':
                            $result[$nr][$key] = (int) $field;
                            break;
                        case 'DOUBLE':
                            $result[$nr][$key] = (float) $field;
                            break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $query
     * @param array  $parameters
     *
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getColumn(string $query, array $parameters = []): array
    {
        $statement = $this->database->prepare($query);
        $this->bindQueryParameters($statement, $parameters);
        $statement->execute();

        return $statement->fetchFirstColumn();
    }

    protected function bindQueryParameters(DriverStatement $statement, array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            switch (gettype($value)) {
                case 'integer':
                    $statement->bindValue($key, $value, PDO::PARAM_INT);
                    break;
                case 'boolean':
                    $statement->bindValue($key, $value, PDO::PARAM_BOOL);
                    break;
                case 'NULL':
                    $statement->bindValue($key, $value, PDO::PARAM_NULL);
                    break;
                default:
                    $statement->bindValue($key, $value);
                    break;
            }
        }
    }

    public function quote($value)
    {
        return $this->database->quote($value);
    }

    /**
     * @param bool   $translateTables
     * @param string $query
     *
     * @return string
     */
    private function translatesTables(bool $translateTables, string $query): string
    {
        if ($translateTables) {
            $query = $this->translator->translate($query);
        }
        return $query;
    }

    /**
     * @param string $query
     *
     * @return Statement
     * @throws DBALException
     */
    private function prepareStatement(string $query): Statement
    {
        $cacheKey = md5($query);
        if (!isset($this->preparedStatements[$cacheKey])) {
            $this->preparedStatements[$cacheKey] = $this->database->prepare($query);
        }

        return $this->preparedStatements[$cacheKey];
    }

    public function beginTransaction(): void
    {
        $this->database->beginTransaction();
    }

    /**
     * @return void
     * @throws ConnectionException
     */
    public function commit(): void
    {
        $this->database->commit();
    }

    /**
     * @return void
     * @throws ConnectionException
     */

    public function rollback(): void
    {
        $this->database->rollBack();
    }
}
