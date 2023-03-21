<?php

namespace Makaira\Connect;

use Makaira\Connect\Utils\TableTranslator;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    protected function getTableTranslatorMock()
    {
        return $this->getMockBuilder(TableTranslator::class)
            ->setConstructorArgs(
                [['oxarticles', 'oxartextends', 'oxattribute', 'oxcategories', 'oxmanufacturers', 'oxobject2attribute']]
            )
            ->setMethodsExcept(['translate'])
            ->disallowMockingUnknownTypes()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();
    }
}
