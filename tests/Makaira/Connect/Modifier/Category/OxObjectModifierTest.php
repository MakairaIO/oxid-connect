<?php

namespace Makaira\Connect\Modifier\Category;

use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Type\Category\Category;
use Makaira\Connect\Type\Category\AssignedOxObject;
use PHPUnit\Framework\TestCase;

class OxObjectModifierTest extends TestCase
{

    public function testApply()
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $dbResult = [
            'oxid' => 'abcdef',
            'oxpos' => 42,
        ];
        $dbMock
            ->expects($this->atLeastOnce())
            ->method('query')
            ->will($this->returnValue([$dbResult]));

        $modifier = new OxObjectModifier($dbMock);

        $category = $modifier->apply(new Category(['id' => 'ghijkl']));

        $this->assertEquals([new AssignedOxObject($dbResult)], $category->oxobject);
    }
}
