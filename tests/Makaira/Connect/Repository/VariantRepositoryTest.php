<?php

namespace Makaira\Connect\Repository;


use Makaira\Connect\Change;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Type\Variant\Variant;
use Makaira\Connect\UnitTestCase;

class VariantRepositoryTest extends UnitTestCase
{
    public function testLoadVariant()
    {
        list($databaseMock, $modifiersMock, $repository) = $this->createVariantRepository();

        $databaseMock
            ->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 42]]);

        $modifiersMock
            ->method('applyModifiers')
            ->will(self::returnArgument(0));

        $change = $repository->get(42);
        self::assertEquals(
            new Change(
                array(
                    'id' => 42,
                    'type' => 'variant',
                    'data' => new Variant(
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
        list($databaseMock, $modifiersMock, $repository) = $this->createVariantRepository();

        $databaseMock
            ->expects(self::once())
            ->method('query')
            ->willReturn([]);

        $modifiersMock
            ->expects(self::never())
            ->method('applyModifiers');

        $change = $repository->get(42);
        self::assertEquals(
            new Change(
                array(
                    'id' => 42,
                    'type' => 'variant',
                    'deleted' => true,
                )
            ),
            $change
        );
    }

    public function testRunModifierLoadVariant()
    {
        list($databaseMock, $modifiersMock, $repository) = $this->createVariantRepository();

        $databaseMock
            ->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 42]]);

        $modifiersMock
            ->expects(self::once())
            ->method('applyModifiers')
            ->willReturn('modified');

        $change = $repository->get(42);
        self::assertEquals(
            new Change(
                array(
                    'id' => 42,
                    'type' => 'variant',
                    'data' => 'modified',
                )
            ),
            $change
        );
    }

    public function testGetAllIds()
    {
        list($databaseMock, , $repository) = $this->createVariantRepository();

        $databaseMock
            ->expects(self::once())
            ->method('query')
            ->willReturn([['OXID' => 42]]);

        self::assertEquals([42], $repository->getAllIds());
    }

    /**
     * @return array
     */
    private function createVariantRepository()
    {
        $databaseMock        = $this->getMock(DatabaseInterface::class);
        $modifiersMock       = $this->getMock(ModifierList::class, [], [], '', false);
        $repository          = new VariantRepository($databaseMock, $modifiersMock, $this->getTableTranslatorMock());

        return [$databaseMock, $modifiersMock, $repository];
    }
}
