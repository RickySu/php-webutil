<?php

namespace WebUtil\Uri;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{

    protected $user;
    protected $password;
    protected $path;
    protected $query;
    protected $host;
    protected $port;
    protected $schema;
    protected $fragment;
    protected $uri;

    static public function createFromArray(array $array)
    {
        $filterterArray = array_merge(array(
            'Schema' => null,
            'Host' => null,
            'Port' => null,
            'Path' => null,
            'Query' => null,
            'Fragment' => null,
            'User' => null,
            'Password' => null,
                ), $array);

        $uri = new static();
        $uri->withUserInfo($filterterArray['User'], $filterterArray['Password']);
        $uri->withScheme($filterterArray['Schema']);
        $uri->withQuery($filterterArray['Query']);
        $uri->withPort($filterterArray['Port']);
        $uri->withPath($filterterArray['Path']);
        $uri->withHost($filterterArray['Host']);
        $uri->withFragment($filterterArray['Fragment']);
        return $uri;
    }

    public function __toString()
    {
        if ($this->uri === null) {
            $this->uri.=$this->schema . '://';

            if ($this->getUserInfo() != '') {
                $this->uri.=$this->getUserInfo() . '@';
            }

            $this->uri.=$this->host;

            $port = $this->port;
            if($this->schema == 'http' && $this->port == 80){
                $port = null;
            }

            if($this->schema == 'https' && $this->port == 443){
                $port = null;
            }

            if ($port != null) {
                $this->uri.=':' . $port;
            }

            $this->uri.=$this->path;

            if ($this->query != '') {
                $this->uri.='?' . $this->query;
            }

            if ($this->fragment != '') {
                $this->uri.='#' . $this->fragment;
            }
        }
        return $this->uri;
    }

    public function getAuthority()
    {
        $authority = $this->getUserInfo() == '' ? '' : $this->getUserInfo() . '@';
        $authority.=$this->host;
        $authority.=$this->port == '' ? '' : ':' . $this->port;
        return $authority;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getScheme()
    {
        return $this->schema;
    }

    public function getUserInfo()
    {
        return urlencode($this->user) . ($this->password === null ? '' : (':' . urlencode($this->password)));
    }

    public function withFragment($fragment)
    {
        $this->fragment = $fragment;
    }

    public function withHost($host)
    {
        $this->host = $host;
    }

    public function withPath($path)
    {
        $this->path = $path;
    }

    public function withPort($port)
    {
        $this->port = $port;
    }

    public function withQuery($query)
    {
        $this->query = $query;
    }

    public function withScheme($scheme)
    {
        $this->schema = $scheme;
    }

    public function withUserInfo($user, $password = null)
    {
        $this->user = $this->user;
        $this->password = $this->password;
    }

}
