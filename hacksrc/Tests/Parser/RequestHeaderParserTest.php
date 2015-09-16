<?php
namespace WebUtil\Tests\Parser;
use WebUtil\Tests\BaseTestCase;

class RequestHeaderParserTest extends BaseTestCase
{

    public function test_forwardHook_nullHook()
    {
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->invokeObjectMethod($parser, 'forwardHook', array('some_data'));
        $this->assertTrue(true); // test no crash
    }

    public function test_forwardHook()
    {
        $parser1 = $this->getMock('WebUtil\\Parser\\RequestHeaderParser', array('feed'));
        $parser1->expects($this->once())
                ->method('feed')
                ->willReturnCallback(function($data){
                    $this->assertEquals('some_data', $data);
                });

        $parser2 = new \WebUtil\Parser\RequestHeaderParser();
        $parser2->setNextHook($parser1);
        $this->invokeObjectMethod($parser2, 'forwardHook', array('some_data'));
        $this->assertTrue(true); // test no crash
    }

    public function test_feed()
    {
        $calls = 0;
        $parser = $this->getMock('WebUtil\\Parser\\RequestHeaderParser', array('parse'));
        $parser->expects($this->any())
                ->method('parse')
                ->willReturnCallback(function() use(&$calls){
                    $calls++;
                });
        $parser->feed('data1');
        $parser->feed('data2');
        $parser->feed('data3');
        $this->assertEquals(3, $calls);
        $this->assertEquals('data1data2data3', $this->getObjectAttribute($parser, 'rawData'));
    }

    public function test_feed_parsed()
    {
        $data = 'some_test_data';
        $parser = $this->getMock('WebUtil\\Parser\\RequestHeaderParser', array('parse', 'forwardHook'));
        $parser->expects($this->never())
                ->method('parse')
                ->willReturn(false);
        $parser->expects($this->once())
                ->method('forwardHook')
                ->willReturnCallback(function($dataForTest) use(&$data){
                    $this->assertEquals($data, $dataForTest);
                });
        $this->setObjectProperty($parser, 'parsed', true);
        $parser->feed($data);
    }

   /**
     * @expectedException WebUtil\Exception\ParserException
     */
    public function test_parse_max_size()
    {
        $testData = str_pad('', 10000, '.');
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', $testData);
        $this->invokeObjectMethod($parser, 'parse');
    }

    public function test_parse_fail()
    {
        $testData = str_pad('', 100, '.');
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', $testData);
        $this->assertFalse($this->invokeObjectMethod($parser, 'parse'));
    }

    public function test_parse_success()
    {
        $testData =
            "GET / HTTP/1.1\r\n".
            "User-Agent: PHP\r\n".
            "Accept: */*\r\n".
            "Host: localhost\r\n".
            "Connection: Keep-Alive\r\n\r\n";
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', $testData);
        $this->assertTrue($this->invokeObjectMethod($parser, 'parse'));
    }

    public function test_splite_header_nodata()
    {
        $testData =
            "GET / HTTP/1.1\r\n".
            "User-Agent: PHP\r\n".
            "Accept: */*\r\n".
            "Host: localhost\r\n".
            "Connection: Keep-Alive\r\n\r\n";
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', $testData);
        $reflactor = new \ReflectionClass($parser);
        $method = $reflactor->getMethod('spliteHeader');
        $method->setAccessible(true);
        $this->assertGreaterThan(0, $method->invokeArgs($parser, array(&$rawHeader, &$data)));
        $this->assertEquals(substr($testData, 0, -4), $rawHeader);
        $this->assertEquals('', $data);
    }

    public function test_splite_header_with_data()
    {
        $testData =
            "GET / HTTP/1.1\r\n".
            "User-Agent: PHP\r\n".
            "Accept: */*\r\n".
            "Host: localhost\r\n".
            "Connection: Keep-Alive\r\n\r\n";
        $additionalData = "additional body data";
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', "$testData$additionalData");
        $reflactor = new \ReflectionClass($parser);
        $method = $reflactor->getMethod('spliteHeader');
        $method->setAccessible(true);
        $this->assertGreaterThan(0, $method->invokeArgs($parser, array(&$rawHeader, &$data)));
        $this->assertEquals(substr($testData, 0, -4), $rawHeader);
        $this->assertEquals($additionalData, $data);
    }

    public function test_parseHeader()
    {
        $testData =
"GET /test.php?a=123&b=alshdkhsa%12 HTTP/1.1\r\n".
"Host: localhost\r\n".
"User-Agent: Mozilla/5.0 Firefox\r\n".
"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
"Accept-Language: zh-TW,en-US;q=0.7,en;q=0.3\r\n".
"Accept-Encoding: gzip, deflate\r\n".
"DNT: 1\r\n".
"Cookie: device_view=mobile;_gat=1\r\n".
"Connection: keep-alive\r\n".
"Cache-Control: max-age=0\r\n";

        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $this->setObjectProperty($parser, 'rawData', $testData);
        $headers = $this->invokeObjectMethod($parser, 'parseHeader', array($testData));
        $this->assertEquals(array(
            'Request' => array(
                'Method' => 'GET',
                'Target' => '/test.php?a=123&b=alshdkhsa%12',
                'Protocol' => 'HTTP',
                'Protocol-Version' => '1.1',
            ),
            'Header' => array(
                'Host' => 'localhost',
                'User-Agent' => 'Mozilla/5.0 Firefox',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'zh-TW,en-US;q=0.7,en;q=0.3',
                'Accept-Encoding' => 'gzip, deflate',
                'DNT' => '1',
                'Cookie' => 'device_view=mobile;_gat=1',
                'Connection' => 'keep-alive',
                'Cache-Control' => 'max-age=0',
            ),
        ), $headers);
    }

}