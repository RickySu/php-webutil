<?php
namespace WebUtil\Tests\Parser;

class RequestHeaderParserTest extends \PHPUnit_Framework_TestCase
{

    public function test_forwardHook_nullHook()
    {
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $reflactor = new \ReflectionClass($parser);
        $method = $reflactor->getMethod('forwardHook');
        $method->setAccessible(true);
        $method->invoke($parser, 'some_data');
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
        $reflactor = new \ReflectionClass($parser2);
        $method = $reflactor->getMethod('forwardHook');
        $method->setAccessible(true);
        $method->invoke($parser2, 'some_data');
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
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('parsed');
        $property->setAccessible(true);
        $property->setValue($parser, true);
        $parser->feed($data);
    }

   /**
     * @expectedException WebUtil\Exception\ParserException
     */
    public function test_parse_max_size()
    {
        $testData = str_pad('', 10000, '.');
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, $testData);
        $method = $reflactor->getMethod('parse');
        $method->setAccessible(true);
        $method->invoke($parser);
    }

    public function test_parse_fail()
    {
        $testData = str_pad('', 100, '.');
        $parser = new \WebUtil\Parser\RequestHeaderParser();
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, $testData);
        $method = $reflactor->getMethod('parse');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($parser));
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
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, $testData);
        $method = $reflactor->getMethod('parse');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($parser));
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
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, $testData);
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
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, "$testData$additionalData");
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
        $reflactor = new \ReflectionClass($parser);
        $property = $reflactor->getProperty('rawData');
        $property->setAccessible(true);
        $property->setValue($parser, $testData);
        $method = $reflactor->getMethod('parseHeader');
        $method->setAccessible(true);
        $headers = $method->invoke($parser, $testData);
        $this->assertEquals(array(
            'request' => array(
                'method' => 'GET',
                'uri' => '/test.php?a=123&b=alshdkhsa%12',
                'protocol' => 'HTTP',
                'protocol-version' => '1.1',
            ),
            'header' => array(
                'host' => 'localhost',
                'user-agent' => 'Mozilla/5.0 Firefox',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'accept-language' => 'zh-TW,en-US;q=0.7,en;q=0.3',
                'accept-encoding' => 'gzip, deflate',
                'dnt' => '1',
                'cookie' => 'device_view=mobile;_gat=1',
                'connection' => 'keep-alive',
                'cache-control' => 'max-age=0',
            ),
        ), $headers);
    }

}