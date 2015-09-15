<?php
namespace WebUtil\Http\Response;

use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    use ResponseTrait;

    protected $protocolVersion = '1.0';
    protected $headers = [];
    protected $headersString;
    protected $body;
    protected $reasonPhrase;
    protected $statusCode;
    protected $prepared = false;

    public function getOutput()
    {
        $this->prepare();
        return
            "HTTP/{$this->protocolVersion} {$this->statusCode} {$this->reasonPhrase}\r\n".
            $this->headersString."\r\n".
            $this->body;
    }
}
