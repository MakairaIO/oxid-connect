<?php

namespace Makaira\Connect\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\DBALException;
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
    private $database;

    /**
     * @var Statement[]
     */
    private $preparedStatements = [];

    /** @var  TableTranslator */
    private $translator;

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
     */
    public function execute(string $query, array $parameters = array(), bool $translateTables = true): int
    {
        $query = $this->translatesTables($translateTables, $query);

        $statement = $this->prepareStatement($query);
        $this->bindQueryParameters($statement, $parameters);

        $statement->execute();
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
     */
    public function query(string $query, array $parameters = array(), bool $translateTables = true): array
    {
        $query     = $this->translatesTables($translateTables, $query);
        $statement = $this->prepareStatement($query);
        $this->bindQueryParameters($statement, $parameters);
        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
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
     */
    public function getColumn(string $query, array $parameters = []): array
    {
        $statement = $this->database->prepare($query);
        $this->bindQueryParameters($statement, $parameters);
        $statement->execute();

        return $statement->fetchColumn();
    }

    protected function bindQueryParameters(DriverStatement $statement, array $parameters)
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

    public function beginTransaction()
    {
        $this->database->beginTransaction();
    }

    /**
     * @return void
     * @throws ConnectionException
     */
    public function commit()
    {
        $this->database->commit();
    }

    /**
     * @return void
     * @throws ConnectionException
     */

    public function rollback()
    {
        $this->database->rollBack();
    }
}
