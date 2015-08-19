<?php
namespace WebUtil\Tests\Parser;
use WebUtil\Tests\BaseTestCase;

class RequestParamParserTest extends BaseTestCase
{

    public function test_initHook()
    {
        $originCallback = function(){};
        $prevParser = $this->getMockForAbstractClass('WebUtil\\Parser\\BaseParser');
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('parse'));
        $parser->expects($this->once())
                ->method('parse')
                ->willReturn(null);
        $prevParser->setOnParsedCallback($originCallback);
        $prevParser->setNextHook($parser);
        $callback = $this->getObjectAttribute($prevParser, 'callback');
        $callback('some_data');
        $this->assertTrue($originCallback !== $callback);
        $this->assertTrue($originCallback === $this->getObjectAttribute($parser, 'callback'));
        $this->assertEquals('some_data', $this->getObjectAttribute($parser, 'parseData'));
    }

}