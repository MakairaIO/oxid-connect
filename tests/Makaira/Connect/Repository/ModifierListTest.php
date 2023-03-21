<?php

namespace Makaira\Connect\Repository;

use Makaira\Connect\Type;
use Makaira\Connect\Modifier;
use PHPUnit\Framework\TestCase;

class ModifierListTest extends TestCase
{
    public function testApplyModifier()
    {
        $modifier = $this->createMock(Modifier::class);
        $type = new Type();

        $modifier
            ->expects($this->once())
            ->method('apply')
            ->with($type)
            ->will($this->returnValue($type));

        $modifierList = new ModifierList('match-no-tag', new \Symfony\Component\EventDispatcher\EventDispatcher());
        $modifierList->addModifier($modifier);
        $result = $modifierList->applyModifiers($type, 'product');

        $this->assertSame($type, $result);
    }
}
