<?php

namespace Makaira\Connect;

use Makaira\Connect\Utils\TableTranslator;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    protected function getTableTranslatorMock()
    {
        return $this->getMock(
            TableTranslator::class,
            ['translate'],
            [['oxarticles', 'oxartextends', 'oxattribute', 'oxcategories', 'oxmanufacturers', 'oxobject2attribute']]
        );
    }
}
