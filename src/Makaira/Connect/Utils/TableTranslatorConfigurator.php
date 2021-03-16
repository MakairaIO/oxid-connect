<?php

namespace Makaira\Connect\Utils;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use oxLang;
use function class_exists;
use function getViewName;
use function is_numeric;

class TableTranslatorConfigurator
{
    /**
     * @var string[]
     */
    private $languageMap;

    /**
     * TableTranslatorConfigurator constructor.
     *
     * @param oxLang $oxLang
     */
    public function __construct(oxLang $oxLang)
    {
        foreach ($oxLang->getLanguageArray() as $language) {
            $this->languageMap[$language->abbr] = $language->id;
        }
    }

    public function configure(TableTranslator $tableTranslator)
    {
        if (class_exists(TableViewNameGenerator::class)) {
            $tableTranslator->setViewNameGenerator(
                function ($table, $language, $shopId = null) {
                    /** @var TableViewNameGenerator $oxid6ViewNameGenerator */
                    $oxid6ViewNameGenerator = Registry::get(TableViewNameGenerator::class);

                    return $oxid6ViewNameGenerator->getViewName($table, $this->mapLanguage($language), $shopId);
                }
            );
        } else {
            $tableTranslator->setViewNameGenerator(
                function ($table, $language, $shopId = null) {
                    return getViewName($table, $this->mapLanguage($language), $shopId);
                }
            );
        }
    }

    /**
     * @param $language
     *
     * @return string|null
     */
    private function mapLanguage($language)
    {
        if (is_numeric($language)) {
            return $language;
        }

        return isset($this->languageMap[$language]) ? $this->languageMap[$language] : null;
    }
}
