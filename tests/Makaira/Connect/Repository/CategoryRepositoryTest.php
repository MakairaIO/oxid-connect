<?php

namespace Makaira\Connect\Repository;

use Makaira\Connect\Change;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Type\Category\Category;
use Makaira\Connect\UnitTestCase;

class CategoryRepositoryTest extends UnitTestCase
{
    public function testLoadCategory()
    {
        $databaseMock  = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new CategoryRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['id' => 42]]);

        $modifiersMock
            ->method('applyModifiers')
            ->will($this->returnArgument(0));

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(
                [
                    'id'   => 42,
                    'type' => 'category',
                    'data' => new Category(
                        [
                            'id' => 42,
                        ]
                    ),
                ]
            ),
            $change
        );
    }

    public function testSetDeletedMarker()
    {
        $databaseMock  = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new CategoryRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([]);

        $modifiersMock
            ->expects($this->never())
            ->method('applyModifiers');

        $change = $repository->get(42);
        $this->assertEquals(
            new Change(
                [
                    'id'      => 42,
                    'type'    => 'category',
                    'deleted' => true,
                ]
            ),
            $change
        );
    }

    public function testRunModifierLoadCategory()
    {
        $databaseMock  = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new CategoryRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

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
            new Change(
                [
                    'id'   => 42,
                    'type' => 'category',
                    'data' => 'modified',
                ]
            ),
            $change
        );
    }

    public function testGetAllIds()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $modifiersMock = $this->createMock(ModifierList::class);

        $repository = new CategoryRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['OXID' => 42]]);

        $this->assertEquals([42], $repository->getAllIds());
    }
}
