<?php
namespace WebUtil\Tests\Parser;

class BaseParserTest extends \PHPUnit_Framework_TestCase
{
    public function test_setOnParsedCallback()
    {
        $callback1 = function(){};
        $callback2 = function(){};
        $parser = $this->getMockForAbstractClass('WebUtil\\Parser\\BaseParser');
        $this->assertEquals(null, $parser->setOnParsedCallback($callback1));
        $this->assertEquals($callback1, $parser->setOnParsedCallback($callback2));
    }
}