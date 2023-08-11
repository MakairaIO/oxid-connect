<?php

namespace Makaira\Connect\Repository;

use Makaira\Connect\Change;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Type\Manufacturer\Manufacturer;
use Makaira\Connect\UnitTestCase;

class ManufacturerRepositoryTest extends UnitTestCase
{
    public function testLoadManufacturer()
    {
        $databaseMock = $this->getMock(DatabaseInterface::class);
        $modifiersMock = $this->getMock(ModifierList::class, [], [], '', false);

        $repository = new ManufacturerRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

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
                array(
                    'id' => 42,
                    'type' => 'manufacturer',
                    'data' => new Manufacturer(
                        array(
                            'id' => 42,
                        )
                    ),
                )
            ),
            $change
        );
    }

    public function testSetDeletedMarker()
    {
        $databaseMock = $this->getMock(DatabaseInterface::class);
        $modifiersMock = $this->getMock(ModifierList::class, [], [], '', false);

        $repository = new ManufacturerRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

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
                array(
                    'id' => 42,
                    'type' => 'manufacturer',
                    'deleted' => true,
                )
            ),
            $change
        );
    }

    public function testRunModifierLoadManufacturer()
    {
        $databaseMock = $this->getMock(DatabaseInterface::class);
        $modifiersMock = $this->getMock(ModifierList::class, [], [], '', false);

        $repository = new ManufacturerRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

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
                array(
                    'id' => 42,
                    'type' => 'manufacturer',
                    'data' => 'modified',
                )
            ),
            $change
        );
    }

    public function testGetAllIds()
    {
        $databaseMock = $this->getMock(DatabaseInterface::class);
        $modifiersMock = $this->getMock(ModifierList::class, [], [], '', false);

        $repository = new ManufacturerRepository($databaseMock, $modifiersMock, $this->getTableTranslator());

        $databaseMock
            ->expects($this->once())
            ->method('query')
            ->willReturn([['OXID' => 42]]);

        $this->assertEquals([42], $repository->getAllIds());
    }
}
