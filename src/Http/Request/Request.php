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
    protected $method;
    protected $request;
    protected $uri;
    protected $server;
    protected $requestTarget;

    /**
     *
     * @param array $array
     * @return static
     */
    static public function createFromArray(array $array, array $server = array())
    {
        $request = new static();
        $request->withRequestTarget($array['request']['target']);
        $request->withServer($server);
        $request->withUri(Uri::createFromArray(array(
            'schema' => $server['https']?'https':'http',
            'host' => $server['server-name'],
            'port' => $server['server-port'],
            'path' => $array['query']['path'],
            'query' => substr($array['request']['target'], strlen($array['query']['path']) + 1),
        )));
        $request->withRequestTarget($array['request']['target']);
        if(isset($array['content'])){
            $request->withBody(new ContentStream($array['content']));
        }
        return $request;
    }

    public function withServer($server)
    {
        $this->server;
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
        return $this->method;
    }

    public function getProtocolVersion()
    {
        return $this->request['protocol-version'];
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
        $name = implode('-', array_map('ucfirst', explode('-', $name)));
        $this->headers[$name] = $value;
    }

    public function withMethod($method)
    {
        $this->method = $method;
    }

    public function withProtocolVersion($version)
    {
        $this->request['protocol-version'] = $version;
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
