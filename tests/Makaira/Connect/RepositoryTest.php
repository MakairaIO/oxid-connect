<?php

namespace Makaira\Connect;

use Makaira\Connect\Repository\AbstractRepository;
use Makaira\Connect\Repository\ModifierList;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RepositoryTest extends UnitTestCase
{
    public function testTouchExecutesQuery()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $repository = new Repository($databaseMock, new EventDispatcher(), false);

        $databaseMock
            ->expects($this->once())
            ->method('execute')
            ->withConsecutive([$this->stringContains('REPLACE INTO'), ['type' => 'product', 'id' => 42]]);

        $repository->touch('product', 42);
    }

    public function testTouchAllOneRepository()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $databaseMock
            ->expects($this->exactly(4))
            ->method('execute')
            ->withConsecutive(
                [$this->stringContains('DELETE FROM')],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 1]],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 2]],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 3]]
                );
        $repositoryMock1 = $this->getMockBuilder(AbstractRepository::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setConstructorArgs(
                [$databaseMock, $this->createMock(ModifierList::class), $this->getTableTranslatorMock()]
            )
            ->getMock();
        $repositoryMock1
            ->expects($this->once())
            ->method('getType')
            ->willReturn('firstRepo');
        $repositoryMock1
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 2, 3]);
        $repository = new Repository($databaseMock, new EventDispatcher(), false);
        $repository->addRepositoryMapping($repositoryMock1);
        $repository->touchAll();
    }

    public function testTouchAllMultipleRepositories()
    {
        $databaseMock = $this->createMock(DatabaseInterface::class);
        $databaseMock
            ->expects($this->exactly(5))
            ->method('execute')
            ->withConsecutive(
                [$this->stringContains('DELETE FROM')],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 1]],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 2]],
                [$this->stringContains('REPLACE INTO'), ['type' => 'firstRepo', 'id' => 3]],
                [$this->stringContains('REPLACE INTO'), ['type' => 'secondRepo', 'id' => 4]]
            );
        $repositoryMock1 = $this->getMockBuilder(AbstractRepository::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setConstructorArgs(
                [$databaseMock, $this->createMock(ModifierList::class), $this->getTableTranslatorMock()]
            )
            ->getMock();
        $repositoryMock1
            ->expects($this->once())
            ->method('getType')
            ->willReturn('firstRepo');
        $repositoryMock1
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 2, 3]);

        $repositoryMock2 = $this->getMockBuilder(AbstractRepository::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setConstructorArgs(
                [$databaseMock, $this->createMock(ModifierList::class), $this->getTableTranslatorMock()]
            )
            ->getMock();
        $repositoryMock2
            ->expects($this->once())
            ->method('getType')
            ->willReturn('secondRepo');
        $repositoryMock2
            ->expects($this->once())
            ->method('getAllIds')
            ->willReturn([4]);
        $repository = new Repository($databaseMock, new EventDispatcher(), false);
        $repository->addRepositoryMapping($repositoryMock1);
        $repository->addRepositoryMapping($repositoryMock2);
        $repository->touchAll();
    }
}
