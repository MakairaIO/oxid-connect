<?php

namespace Makaira\Connect\Rpc\Handler;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\Connect\HttpException;
use Makaira\Connect\Repository;
use Makaira\Connect\Rpc\HandlerInterface;
use Makaira\Connect\Utils\TableTranslator;
use OxidEsales\Eshop\Core\Language;

use function array_flip;

class GetUpdates implements HandlerInterface
{
    private $language;

    private $tableTranslator;

    private $repository;

    public function __construct(
        Language $language,
        TableTranslator $tableTranslator,
        Repository $repository
    ) {
        $this->repository      = $repository;
        $this->tableTranslator = $tableTranslator;
        $this->language        = $language;
    }

    /**
     * @param array $request
     *
     * @return array
     * @throws HttpException
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function handle(array $request): array
    {
        if (!isset($request['since'])) {
            throw new HttpException(400);
        }

        $language  = $request['language'] ?? $this->language->getLanguageAbbr();
        $languages = array_flip($this->language->getLanguageIds());
        if (isset($languages[$language])) {
            $this->language->setBaseLanguage($languages[$language]);
        }

        $this->tableTranslator->setLanguage($language);

        return $this->repository->getChangesSince((int) $request['since'], (int) ($request['count'] ?? 50));
    }
}
