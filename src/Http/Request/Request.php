<?php
namespace WebUtil\Http\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use WebUtil\Stream\ContentStream;
use WebUtil\Uri\Uri;

class Request implements RequestInterface
{
    protected $body;
    protected $headers;
    protected $request;
    protected $uri;
    protected $server;
    protected $requestTarget;

    /**
     *
     * @param array $array
     * @return Request
     */
    static public function createFromArray(array $array, array $server = array())
    {
        $request = new static();
        $request->withRequestTarget($array['Request']['Target']);
        $request->withServer($server);
        $request->withUri(Uri::createFromArray(array(
            'Schema' => $server['Https']?'https':'http',
            'Host' => $server['Server-Name'],
            'Port' => $server['Server-Port'],
            'Path' => $array['Query']['Path'],
            'Query' => substr($array['Request']['Target'], strlen($array['Query']['Path']) + 1),
        )));
        $request->withServer($server);
        $request->withRequestTarget($array['Request']['Target']);
        if(isset($array['Content'])){
            $request->withBody(new ContentStream($array['Content']));
        }
        return $request;
    }

    public function withServer($server)
    {
        $this->server = $server;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    public function getHeaderLine($name)
    {   $header = isset($this->headers[$name]) ? $this->headers[$name] : null;
        if ($header === null) {
            return null;
        }
        return "$name: $header";
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getMethod()
    {
        return $this->request['Method'];
    }

    public function getProtocolVersion()
    {
        return $this->request['Protocol-Version'];
    }

    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function withAddedHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function withMethod($method)
    {
        $this->request['Method'] = $method;
    }

    public function withProtocolVersion($version)
    {
        $this->request['Protocol-Version'] = $version;
    }

    public function withRequestTarget($requestTarget)
    {
        $this->requestTarget = $requestTarget;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;
    }

    public function withoutHeader($name)
    {
        unset($this->headers[$name]);
    }

}
