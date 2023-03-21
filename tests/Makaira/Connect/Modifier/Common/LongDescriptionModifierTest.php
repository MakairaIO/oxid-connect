<?php

namespace Makaira\Connect\Modifier\Common;


use Makaira\Connect\Type\Common\BaseProduct;
use Makaira\Connect\DatabaseInterface;
use Makaira\Connect\Utils\ContentParserInterface;
use PHPUnit\Framework\TestCase;

class LongDescriptionModifierTest extends TestCase
{

    public function testShortText()
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->expects($this->once())
            ->method('parseContent')
            ->will($this->returnArgument(0));
        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new BaseProduct();
        $product->longdesc = 'This is a short text';
        $product = $modifier->apply($product, $dbMock);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

    public function testShortTextWithHTML()
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->expects($this->once())
            ->method('parseContent')
            ->will($this->returnArgument(0));
        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new BaseProduct();
        $product->longdesc = 'This is a <del>short</del> text';
        $product = $modifier->apply($product, $dbMock);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

    public function testTrimming()
    {
        $dbMock = $this->createMock(DatabaseInterface::class);
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->expects($this->once())
            ->method('parseContent')
            ->will($this->returnArgument(0));
        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new BaseProduct();
        $product->longdesc = '   This is a short text   ' . PHP_EOL;
        $product = $modifier->apply($product, $dbMock);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

}
