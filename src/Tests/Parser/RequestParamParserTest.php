<?php

namespace WebUtil\Tests\Parser;

use WebUtil\Tests\BaseTestCase;
use WebUtil\Parser\RequestParamParser;

class RequestParamParserTest extends BaseTestCase
{

    public function test_initHook()
    {
        $originCallback = function() {

        };
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
                ->willReturn(function($data) {
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
                ->willReturnCallback(function($data) use(&$dataGlobal) {
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

    public function test_parse_no_content_length()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed'));
        $parser->expects($this->never())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->never())
                ->method('setParsed')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'parseData', array('header' => array()));
        $this->invokeObjectMethod($parser, 'parse');
    }

    /**
     * @expectedException WebUtil\Exception\ParserException
     */
    public function test_parse_content_length_too_large()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed'));
        $parser->expects($this->never())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->never())
                ->method('setParsed')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'parseData', array(
            'header' => array('content-length' => 5)
        ));
        $this->setObjectProperty($parser, 'rawData', '123456');
        $this->invokeObjectMethod($parser, 'parse');
    }

    public function test_parse_bad_content_type()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed', 'parseContentType'));
        $parser->expects($this->never())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->never())
                ->method('setParsed')
                ->willReturn(null);
        $parser->expects($this->once())
                ->method('parseContentType')
                ->willReturn(false);
        $this->setObjectProperty($parser, 'parseData', array(
            'header' => array(
                'content-length' => 5,
                'content-type' => '',
            )
        ));
        $this->setObjectProperty($parser, 'rawData', '12345');
        $this->invokeObjectMethod($parser, 'parse');
    }

    public function test_parse_parseContent()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed', 'parseContentType', 'parseContent'));
        $parser->expects($this->never())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->never())
                ->method('setParsed')
                ->willReturn(null);
        $parser->expects($this->once())
                ->method('parseContentType')
                ->willReturn(array('application/json', 'charset=utf-8'));
        $parser->expects($this->once())
                ->method('parseContent')
                ->willReturn(true);
        $this->setObjectProperty($parser, 'parseData', array(
            'header' => array(
                'content-length' => 5,
                'content-type' => '',
            )
        ));
        $this->setObjectProperty($parser, 'rawData', '12345');
        $this->invokeObjectMethod($parser, 'parse');
    }


    public function test_parse()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed', 'parseContentType', 'parseContent'));
        $parser->expects($this->once())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->once())
                ->method('setParsed')
                ->willReturn(null);
        $parser->expects($this->once())
                ->method('parseContentType')
                ->willReturn(array('application/json', 'charset=utf-8'));
        $parser->expects($this->once())
                ->method('parseContent')
                ->willReturn(false);
        $this->setObjectProperty($parser, 'parseData', array(
            'header' => array(
                'content-length' => 5,
                'content-type' => '',
            )
        ));
        $this->setObjectProperty($parser, 'rawData', '12345');
        $this->invokeObjectMethod($parser, 'parse');

    }
}
