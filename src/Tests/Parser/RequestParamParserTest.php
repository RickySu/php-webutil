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
        $parser->expects($this->once())
                ->method('forwardHook')
                ->willReturn(null);
        $parser->expects($this->once())
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

    public function test_setParsed_no_callback()
    {
        $parser = new RequestParamParser();
        $this->setObjectProperty($parser, 'callback', null);
        $this->setObjectProperty($parser, 'parseData', 'parse_data');
        $this->setObjectProperty($parser, 'parsed', false);
        $this->invokeObjectMethod($parser, 'setParsed');
        $this->assertEquals(null, $this->getObjectAttribute($parser, 'parseData'));
        $this->assertTrue($this->getObjectAttribute($parser, 'parsed'));
    }

    public function test_setParsed_with_callback()
    {
        $calls = false;
        $parser = new RequestParamParser();
        $this->setObjectProperty($parser, 'callback', function($parseData) use(&$calls){
            $this->assertEquals('parse_data', $parseData);
            $calls = true;
        });
        $this->setObjectProperty($parser, 'parseData', 'parse_data');
        $this->setObjectProperty($parser, 'parsed', false);
        $this->invokeObjectMethod($parser, 'setParsed');
        $this->assertEquals(null, $this->getObjectAttribute($parser, 'parseData'));
        $this->assertTrue($this->getObjectAttribute($parser, 'parsed'));
        $this->assertTrue($calls);
    }

    public function test_parseContentType_no_part()
    {
        $parser = new RequestParamParser();
        $result = $this->invokeObjectMethod($parser, 'parseContentType', array('application/x-www-form-urlencoded'));
        $this->assertEquals(array('application/x-www-form-urlencoded', null), $result);
    }

    public function test_parseContentType_with_part()
    {
        $parser = new RequestParamParser();
        $result = $this->invokeObjectMethod($parser, 'parseContentType', array('application/json;charset=utf-8'));
        $this->assertEquals(array('application/json', 'charset=utf-8'), $result);
    }

    public function test_parseContent_urlencode()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('parseUrlEncode', 'setParsed'));
        $parser->expects($this->once())
                ->method('parseUrlEncode')
                ->willReturnCallback(function($data){
                    $this->assertEquals('raw_url_enc_data', $data);
                    return 'url_dec_data';
                });
        $parser->expects($this->once())
                ->method('setParsed')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'rawData', 'raw_url_enc_data');
        $this->assertTrue($this->invokeObjectMethod($parser, 'parseContent', array(
            'application/x-www-form-urlencoded',
            null,
            strlen('raw_url_enc_data'),
        )));
        $this->assertEquals('raw_url_enc_data', $this->getObjectAttribute($parser, 'parseData')['content']);
        $this->assertEquals('url_dec_data', $this->getObjectAttribute($parser, 'parseData')['content-parsed']);
    }

    public function test_parseContent_jsonencode()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('parseJSONEncode', 'setParsed'));
        $parser->expects($this->once())
                ->method('parseJSONEncode')
                ->willReturnCallback(function($data){
                    $this->assertEquals('raw_json_enc_data', $data);
                    return 'json_dec_data';
                });
        $parser->expects($this->once())
                ->method('setParsed')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'rawData', 'raw_json_enc_data');
        $this->assertTrue($this->invokeObjectMethod($parser, 'parseContent', array(
            'application/json',
            'charset=utf-8',
            strlen('raw_json_enc_data'),
        )));
        $this->assertEquals('raw_json_enc_data', $this->getObjectAttribute($parser, 'parseData')['content']);
        $this->assertEquals('json_dec_data', $this->getObjectAttribute($parser, 'parseData')['content-parsed']);
    }

    public function test_parseContent_multipart()
    {
        $parser = $this->getMock('WebUtil\\Parser\\RequestParamParser', array('forwardHook', 'setParsed'));
        $parser->expects($this->once())
                ->method('forwardHook')
                ->willReturnCallback(function($data){
                    $this->assertEquals('raw_multipart_enc_data', $data);
                    return null;
                });
        $parser->expects($this->once())
                ->method('setParsed')
                ->willReturn(null);
        $this->setObjectProperty($parser, 'rawData', 'raw_multipart_enc_data');
        $this->assertTrue($this->invokeObjectMethod($parser, 'parseContent', array(
            'multipart/form-data',
            'boundary=1234567',
            strlen('raw_multipart_enc_data'),
        )));
        $this->assertEquals('1234567', $this->getObjectAttribute($parser, 'parseData')['content-boundary']);
    }

    public function test_parseContent_unknown()
    {
        $parser = new RequestParamParser();
        $this->assertFalse($this->invokeObjectMethod($parser, 'parseContent', array(
            'unknown/unknown',
            null,
            strlen('raw_multipart_enc_data'),
        )));
    }

    public function test_parseJSONEncode()
    {
        $parser = new RequestParamParser();
        $data = array(
            'a' => 'b',
            'b' => array(
                'a' => 'a',
                'b' => 'b',
            ),
        );
        $this->assertEquals($data, $this->invokeObjectMethod($parser, 'parseJSONEncode', array(json_encode($data))));
    }

    public function test_parseUrlEncode()
    {
        $parser = new RequestParamParser();
        $data = array(
            'a' => 'a',
            'b' => 'b',
            'c' => 'zbcd!@#@%$2345&',
        );
        $this->assertEquals($data, $this->invokeObjectMethod($parser, 'parseUrlEncode', array(http_build_query($data))));
    }
}
