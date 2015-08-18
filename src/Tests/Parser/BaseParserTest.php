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

    public function test_setNextHook()
    {
        $parser1 = $this->getMockForAbstractClass('WebUtil\\Parser\\BaseParser');
        $parser2 = $this->getMockForAbstractClass('WebUtil\\Parser\\BaseParser', array('initHook'), '', false);
        $parser2->expects($this->once())
                ->method('initHook')
                ->willReturnCallback(function($parser) use($parser1){
                    $this->assertEquals($parser1, $parser);
                });
        $parser1->setNextHook($parser2);
        $this->assertEquals($parser2, $this->getObjectAttribute($parser1, 'nextHook'));
    }
}