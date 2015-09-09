<?php
namespace WebUtil\Http\Request;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected $query;
    protected $cookies;
    protected $parsedBody;
    protected $attributes;
    protected $files;

    /**
     *
     * @param array $array
     * @return static
     */
    static public function createFromArray(array $array, array $server = array())
    {
        $request = parent::createFromArray($array, $server);
        $request->withRequest($array['request']);
        $request->withHeaders($array['header']);
        $request->withQueryParams($array['query']);
        $request->withParsedBody(isset($array['content-parsed'])?$array['content-parsed']:array());
        return $request;
    }

    public function withRequest($request)
    {
        $this->request = $request;
    }

    public function withHeaders(array $headers)
    {
        $this->headers = $headers;
        $this->withCookieParams(isset($headers['cookie'])?$headers['cookie']:array());
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getCookieParams()
    {
        return $this->cookies;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function getQueryParams()
    {
        return $this->query['params']['param'];
    }

    public function getServerParams()
    {

    }

    public function getUploadedFiles()
    {
        return $this->files;
    }

    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function withCookieParams(array $cookies)
    {
        $this->cookies = $cookies;
    }

    public function withParsedBody($data)
    {
        $this->parsedBody = $data;
    }

    public function withQuery(array $query)
    {
        $this->query = $query;
    }

    public function withQueryParams(array $query)
    {
        $this->query['params'] = $query;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->files = $uploadedFiles;
    }

    public function withoutAttribute($name)
    {
        unset($this->attributes['name']);
    }

}
