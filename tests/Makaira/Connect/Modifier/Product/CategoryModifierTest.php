<?php

namespace Makaira\Connect\Modifier\Product;

use Makaira\Connect\Type\Common\AssignedCategory;
use Makaira\Connect\Type\Product\Product;
use Makaira\Connect\DatabaseInterface;
use PHPUnit\Framework\TestCase;

class CategoryModifierTest extends TestCase
{
    public function testUnnested(): void
    {
        $queryCb = static function (...$args) {
            if ('abc' === ($args[1]['productId'] ?? '') && $args[1]['productActive'] ?? false) {
                return [
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
                ];
            }

            if (5 === ($args[1]['left'] ?? 0) && 7 === ($args[1]['right'] ?? 0) && 42 === ($args[1]['rootId'] ?? 0)) {
                return [
                    [
                        'title'  => 'mytitle',
                        'active'  => 1
                    ],
                ];
            }

            return [];
        };
        $dbMock = $this->createMock(DatabaseInterface::class);
        $dbMock
            ->method('query')
            ->willReturnCallback($queryCb);

        $product           = new Product();
        $product->id       = 'abc';
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
            ],
            $product->category
        );
    }
}
