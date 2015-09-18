<?hh

namespace WebUtil\Uri;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{

    protected ?string $user;
    protected ?string $password;
    protected ?string $path;
    protected ?string $query;
    protected ?string $host;
    protected ?string $port;
    protected ?string $schema;
    protected ?string $fragment;
    protected ?string $uri;

    static public function createFromArray(array $array) :Uri
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

        $uri = new self();
        $uri->withUserInfo($filterterArray['User'], $filterterArray['Password']);
        $uri->withScheme($filterterArray['Schema']);
        $uri->withQuery($filterterArray['Query']);
        $uri->withPort($filterterArray['Port']);
        $uri->withPath($filterterArray['Path']);
        $uri->withHost($filterterArray['Host']);
        $uri->withFragment($filterterArray['Fragment']);
        return $uri;
    }

    public function __toString() :string
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

    public function getAuthority() :string
    {
        $authority = $this->getUserInfo() == '' ? '' : $this->getUserInfo() . '@';
        $authority.=$this->host;
        $authority.=$this->port == '' ? '' : ':' . $this->port;
        return $authority;
    }

    public function getFragment() :?string
    {
        return $this->fragment;
    }

    public function getHost() :?string
    {
        return $this->host;
    }

    public function getPath() :?string
    {
        return $this->path;
    }

    public function getPort() :?string
    {
        return $this->port;
    }

    public function getQuery() :?string
    {
        return $this->query;
    }

    public function getScheme() :?string
    {
        return $this->schema;
    }

    public function getUserInfo() :string
    {
        return urlencode($this->user) . ($this->password === null ? '' : (':' . urlencode($this->password)));
    }

    public function withFragment($fragment) :void
    {
        $this->fragment = (string) $fragment;
    }

    public function withHost($host) :void
    {
        $this->host = (string) $host;
    }

    public function withPath($path) :void
    {
        $this->path = (string) $path;
    }

    public function withPort($port) :void
    {
        $this->port = (string) $port;
    }

    public function withQuery($query) :void
    {
        $this->query = (string) $query;
    }

    public function withScheme($scheme) :void
    {
        $this->schema = (string) $scheme;
    }

    public function withUserInfo($user, $password = null) :void
    {
        $this->user = (string) $user;
        $this->password = (string) $password;
    }

}
