<?php

namespace Makaira\Connect\Modifier\Product;

use Makaira\Connect\Type\Common\AssignedCategory;
use Makaira\Connect\Type\Product\Product;
use Makaira\Connect\DatabaseInterface;
use PHPUnit\Framework\TestCase;

class CategoryModifierTest extends TestCase
{
    public function testUnnested()
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $dbMock
            ->expects($this->at(0))
            ->method('query')
            ->with($this->anything(), ['productId' => 'abc', 'productActive' => 1])
            ->will(
                $this->returnValue(
                    [
                        [
                            'catid'  => 'def',
                            'title'  => 'mysubcat',
                            'oxpos'  => 1,
                            'shopid' => 1,
                            'active' => 1,
                            'oxleft' => 5,
                            'oxright' => 7,
                            'oxrootid' => 42,
                        ],
                    ]
                )
            );
        $dbMock
            ->expects($this->at(1))
            ->method('query')
            ->with($this->anything(), ['left' => 5, 'right' => 7, 'rootId' => 42])
            ->will(
                $this->returnValue(
                    [
                        [
                            'title'  => 'mytitle',
                            'active'  => 1
                        ],
                    ]
                )
            );

        $product = new Product();
        $product->id = 'abc';
        $product->OXACTIVE = 1;

        $modifier = new CategoryModifier($dbMock);

        $product = $modifier->apply($product);

        $this->assertEquals(
            [
                new AssignedCategory(
                    [
                        'catid'  => 'def',
                        'pos'  => 1,
                        'shopid' => 1,
                        'path' => 'mytitle/',
                        'title' => 'mysubcat'
                    ]
                ),
            ], $product->category
        );
    }
}
