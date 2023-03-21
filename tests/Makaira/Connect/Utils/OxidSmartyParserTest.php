<?php

namespace Makaira\Connect\Utils;


use PHPUnit\Framework\TestCase;

class OxidSmartyParserTest extends TestCase
{

    public function testLanguageSetting()
    {
        $langMock = $this->createMock(\oxLang::class);
        $langMock
            ->expects($this->once())
            ->method('setTplLanguage')
            ->with(4);
        $utilsViewMock = $this->createMock(\oxUtilsView::class);
        $parser = new OxidSmartyParser($langMock, $utilsViewMock);
        $parser->setTplLang(4);
    }

    public function testParsing()
    {
        $langMock = $this->createMock(\oxLang::class);
        $utilsViewMock = $this->createMock(\oxUtilsView::class);
        $utilsViewMock
            ->expects($this->once())
            ->method('parseThroughSmarty')
            ->with('foo')
            ->willReturn('bar');

        $parser = new OxidSmartyParser($langMock, $utilsViewMock);
        $this->assertEquals('bar', $parser->parseContent('foo'));
    }

}
