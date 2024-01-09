<?php

namespace Makaira\Connect\Modifier\Product;

use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Exception as ConnectException;
use Makaira\Connect\Type;
use Makaira\Connect\Type\Product\Product;
use PHPUnit\Framework\TestCase;

use Spatie\Snapshots\MatchesSnapshots;

use function str_contains;

class VariantAttributesModifierTest extends TestCase
{
    use MatchesSnapshots;

    public function testThrowsExceptionIfProductIdIsNotSet()
    {
        $modifier = new VariantAttributesModifier(
            $this->createMock(DatabaseInterface::class),
            'OXACTIVE = 1',
            [],
            []
        );

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage("Cannot fetch attributes without a product ID.");

        $modifier->apply(new Type());
    }

    /**
     * @param string $dbCallback
     *
     * @return void
     * @throws ConnectException
     * @dataProvider provideDbCallback
     */
    public function testFethcesAttributes(string $dbCallback)
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $dbMock->method('query')
            ->willReturnCallback([$this, $dbCallback]);

        $modifier = new VariantAttributesModifier(
            $dbMock,
            'OXACTIVE = 1',
            ['intAttr', '111ebeb2f08072eef3b164f0dc7ab653'],
            ['floatAttr', '01aa4923c4110ef347161f848a9d36aa']
        );

        $product = new Product(['id' => 'phpunit_product']);
        $modifier->apply($product);

        $this->assertMatchesSnapshot($product);
    }

    public function provideDbCallback()
    {
        return [
            'Product with variants' => ['productWithVariantsCallback'],
            'Product without variants' => ['productWithoutVariantsCallback'],
        ];
    }

    public function productWithVariantsCallback(...$args)
    {
        if (str_contains($args[0], 'oxvarname')) {
            return [['oxvarname' => 'size|color|intAttr|floatAttr']];
        }

        if (str_contains($args[0], 'oxvarselect')) {
            return [
                ['id' => 'phpunit_variant1', 'value' => 'S|red|11|1.1'],
                ['id' => 'phpunit_variant2', 'value' => 'M|green|22|2.2'],
                ['id' => 'phpunit_variant3', 'value' => 'L|blue|33|3.3'],
            ];
        }

        if (str_contains($args[0], 'oxvalue')) {
            return [
                ['id' => 'a1', 'value' => 'p'],
                ['id' => 'a2', 'value' => 'v1'],
                ['id' => 'a3', 'value' => 'v2'],
                ['id' => 'a4', 'value' => 'v3'],
            ];
        }

        return [];
    }

    public function productWithoutVariantsCallback(...$args)
    {
        if (str_contains($args[0], 'oxvarname')) {
            return [['oxvarname' => '']];
        }

        if (str_contains($args[0], 'oxvarselect')) {
            return [];
        }

        if (str_contains($args[0], 'oxvalue')) {
            return [
                ['id' => 'intAttr', 'value' => '1337'],
                ['id' => 'floatAttr', 'value' => '13.37'],
                ['id' => 'stringAttr', 'value' => 'phpunit'],
            ];
        }

        return [];
    }
}
