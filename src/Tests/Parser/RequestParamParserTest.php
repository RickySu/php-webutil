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

    public function test_forwardHook()
    {
        $parser1 = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('feed'));
        $parser1->expects($this->once())
                ->method('feed')
                ->willReturn(function($data){
                    $this->assertEquals('some_data', $data);
                });
        $parser = new \WebUtil\Parser\RequestParamParser();
        $parser->setNextHook($parser1);
        $this->invokeObjectMethod($parser, 'forwardHook', array('some_data'));
    }

    public function test_feed_none_parsed()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('parse', 'forwardHook'));
        $parser->expects($this->never())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->exactly(3))
                ->method('parse')
                ->willReturn(null);
        $parser->feed('a');
        $parser->feed('b');
        $parser->feed('c');
        $this->assertEquals('abc', $this->getObjectAttribute($parser, 'rawData'));
    }

    public function test_feed_parsed()
    {
        $dataGlobal = '';
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('parse', 'forwardHook'));
        $parser->expects($this->exactly(3))
                ->method('forwardHook')
                ->willReturnCallback(function($data) use(&$dataGlobal){
                    $dataGlobal.=$data;
                });
        $parser->expects($this->never())
                ->method('parse')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'parsed', true);
        $parser->feed('a');
        $parser->feed('b');
        $parser->feed('c');
        $this->assertEquals('abc', $dataGlobal);
    }

    public function test_parseSemicolonField()
    {
        $originData = ' ak;;s!@#%%&%&*%^78';
        $rawData = 'a=123 ; b = 456;c='. rawurlencode($originData);
        $parser = new \WebUtil\Parser\RequestParamParser();
        $result = $this->invokeObjectMethod($parser, 'parseSemicolonField', array($rawData));
        $this->assertEquals(array(
            'a' => '123',
            'b' => '456',
            'c' => $originData,
        ), $result);
    }

}