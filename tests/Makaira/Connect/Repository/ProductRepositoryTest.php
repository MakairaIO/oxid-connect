<?php

namespace Makaira\Connect\Repository;

use Makaira\Connect\Change;
use Makaira\Connect\Type\Product\Product;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\UnitTestCase;

class ProductRepositoryTest extends UnitTestCase
{
    public function testLoadProduct()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new ProductRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['id' => 42]]);

        $modifiersMock
            ->method('applyModifiers')
            ->will($this->returnArgument(0));

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'data' => new Product(array(
                    'id' => 42,
                )),
            )),
            $change
        );
    }

    public function testSetDeletedMarker()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new ProductRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([]);

        $modifiersMock
            ->expects($this->never())
            ->method('applyModifiers');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'deleted' => true,
            )),
            $change
        );
    }

    public function testRunModifierLoadProduct()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new ProductRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['id' => 42]]);

        $modifiersMock
            ->expects($this->once())
            ->method('applyModifiers')
            ->willReturn('modified');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(array(
                'id' => 42,
                'type' => 'product',
                'data' => 'modified',
            )),
            $change
        );
    }

    public function testGetAllIds()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new ProductRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['OXID' => 42]]);

        $this->assertEquals([42], $repository->getAllIds());
    }
}
