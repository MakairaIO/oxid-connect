<?php

namespace Makaira\Connect\Utils;

use Closure;

class TableTranslator
{
    /**
     * @var string[]
     */
    private $searchTables;

    /**
     * @var string
     */
    private $language = 'de';

    /**
     * @var
     */
    private $shopId;

    /**
     * @var Closure
     */
    private $viewNameGenerator;

    /**
     * TableTranslator constructor.
     *
     * @param string[] $searchTables
     */
    public function __construct(array $searchTables)
    {
        $this->searchTables = $searchTables;

        $this->viewNameGenerator = static function ($table, $language, $shopId = null) {
            if (null !== $shopId) {
                $table = "{$table}_{$shopId}";
            }

            return "oxv_{$table}_{$language}";
        };
    }

    /**
     * @param Closure $viewNameGenerator
     *
     * @return TableTranslator
     */
    public function setViewNameGenerator(Closure $viewNameGenerator): TableTranslator
    {
        $this->viewNameGenerator = $viewNameGenerator;

        return $this;
    }

    /**
     * Set the language
     *
     * @param string $language
     *
     * @return TableTranslator
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param mixed $shopId
     *
     * @return TableTranslator
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Translate an sql query
     *
     * @param string $sql
     *
     * @return string
     */
    public function translate($sql)
    {
        foreach ($this->searchTables as $searchTable) {
            $replaceTable = ($this->viewNameGenerator)($searchTable, $this->language, $this->shopId);
            $sql          = preg_replace_callback(
                "((?P<tableName>{$searchTable})(?P<end>[^A-Za-z0-9_]|$))",
                static function ($match) use ($replaceTable) {
                    return $replaceTable . $match['end'];
                },
                $sql
            );
        }

        return $sql;
    }
}
