<?php
namespace WebUtil\Tests\Http;
use WebUtil\Tests\BaseTestCase;
use WebUtil\Http\ServerRequest;

class RequestTest extends BaseTestCase
{
    public function test_withCookieParams()
    {
        $parser = new ServerRequest();
        $testArray = array(
            'a' => 'test1',
            'b' => 'test2',
            'c' => 'test3',
        );
        $parser->withCookieParams($testArray);
        $this->assertEquals($testArray, $this->getObjectAttribute($parser, 'cookies'));
    }

    public function test_getCookieParams()
    {
        $parser = new ServerRequest();
        $testArray = array(
            'a' => 'test1',
            'b' => 'test2',
            'c' => 'test3',
        );
        $parser->withCookieParams($testArray);
        $this->assertEquals($testArray, $parser->getCookieParams());
    }
}