<?hh
namespace WebUtil\Http\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use WebUtil\Stream\ContentStream;
use WebUtil\Uri\Uri;

class Request //implements RequestInterface
{
    protected ?StreamInterface $body;
    protected array $headers = [];
    protected array<string, string> $request = [];
    protected ?UriInterface $uri;
    protected array<string, string> $server = [];
    protected string $requestTarget = '';

    /**
     *
     * @param array $array
     * @return Request
     */
    static public function createFromArray(array $array, array $server = array()) :Request
    {
        $request = new self();
        self::initFromArray($request, $array, $server);
        return $request;
    }

    static protected function initFromArray(Request $request, array $array, array $server) :void
    {
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
    }

    public function withServer($server) :void
    {
        $this->server = (array) $server;
    }

    public function getServer() :array<string, string>
    {
        return $this->server;
    }

    public function getBody() :?StreamInterface
    {
        return $this->body;
    }

    public function getHeader($name) :?string
    {
        return isset($this->headers[(string) $name]) ? $this->headers[(string) $name] : null;
    }

    public function getHeaderLine($name) :?string
    {   $header = isset($this->headers[(string) $name]) ? $this->headers[(string) $name] : null;
        if ($header === null) {
            return null;
        }
        return "$name: $header";
    }

    public function getHeaders() :array
    {
        return $this->headers;
    }

    public function getMethod() :?string
    {
        return $this->request['Method'];
    }

    public function getProtocolVersion() :?string
    {
        return $this->request['Protocol-Version'];
    }

    public function getRequestTarget() :?string
    {
        return $this->requestTarget;
    }

    public function getUri() :?UriInterface
    {
        return $this->uri;
    }

    public function hasHeader($name) :bool
    {
        return isset($this->headers[(string) $name]);
    }

    public function withAddedHeader($name, $value) :void
    {
        $this->headers[(string) $name] = (string) $value;
    }

    public function withBody(StreamInterface $body) :void
    {
        $this->body = $body;
    }

    public function withHeader($name, $value) :void
    {
        $this->headers[(string) $name] = (string) $value;
    }

    public function withMethod($method) :void
    {
        $this->request['Method'] = (string) $method;
    }

    public function withProtocolVersion($version) :void
    {
        $this->request['Protocol-Version'] = (string) $version;
    }

    public function withRequestTarget($requestTarget) :void
    {
        $this->requestTarget = (string) $requestTarget;
    }

    public function withUri(UriInterface $uri, $preserveHost = false) :void
    {
        $this->uri = $uri;
    }

    public function withoutHeader($name) :void
    {
        unset($this->headers[(string) $name]);
    }

}
