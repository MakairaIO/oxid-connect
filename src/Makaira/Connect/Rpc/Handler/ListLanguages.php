<?php

namespace Makaira\Connect\Rpc\Handler;

use Makaira\Connect\Rpc\HandlerInterface;
use OxidEsales\Eshop\Core\Language;

class ListLanguages implements HandlerInterface
{
    private $language;

    /**
     * @param Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string>
     */
    public function handle(array $request): array
    {
        return $this->language->getLanguageIds();
    }
}
