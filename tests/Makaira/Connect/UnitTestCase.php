<?php

namespace Makaira\Connect;

use Makaira\Connect\Utils\TableTranslator;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    protected function getTableTranslator(): TableTranslator
    {
        return new TableTranslator(
            ['oxarticles', 'oxartextends', 'oxattribute', 'oxcategories', 'oxmanufacturers', 'oxobject2attribute']
        );
    }
}
