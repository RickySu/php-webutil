<?hh
namespace WebUtil\Http\Request;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected array<string, array> $query = [];
    protected array<string, string> $cookies = [];
    protected array<string, string> $parsedBody = [];
    protected array<string, string> $attributes = [];
    protected array $files = [];

    /**
     *
     * @param array $array
     * @return static
     */
    static public function createFromArray(array<string, array> $array, array $server = array()) :ServerRequest
    {
        $request = new self();
        parent::initFromArray($request, $array, $server);
        $request->withRequest($array['Request']);
        $request->withHeaders($array['Header']);
        $request->withQueryParams($array['Query']);
        $request->withParsedBody(isset($array['Content-Parsed'])?$array['Content-Parsed']:array());
        return $request;
    }

    public function withRequest(array<string, string> $request) :void
    {
        $this->request = $request;
    }

    public function withHeaders(array $headers) :void
    {
        $this->headers = $headers;
        $this->withCookieParams(isset($headers['Cookie'])?$headers['Cookie']:array());
    }

    public function getAttribute($name, $default = null) :?string
    {
        return isset($this->attributes[(string) $name]) ? $this->attributes[(string) $name] : $default;
    }

    public function getAttributes() :array<string, string>
    {
        return $this->attributes;
    }

    public function getCookieParams() :array<string, string>
    {
        return $this->cookies;
    }

    public function getParsedBody() :?array<string, string>
    {
        return $this->parsedBody;
    }

    public function getQueryParams() :?array<string, string>
    {
        return $this->query['Params']['Param'];
    }

    public function getServerParams() :?array<string, string>
    {
        return $this->server;
    }

    public function getUploadedFiles() :?array
    {
        return $this->files;
    }

    public function withAttribute($name, $value) :void
    {
        $this->attributes[(string) $name] = (string) $value;
    }

    public function withCookieParams(array $cookies) :void
    {
        $this->cookies = $cookies;
    }

    public function withParsedBody($data) :void
    {
        $this->parsedBody = (array) $data;
    }

    public function withQuery(array $query) :void
    {
        $this->query = $query;
    }

    public function withQueryParams(array $query) :void
    {
        $this->query['Params'] = $query;
    }

    public function withUploadedFiles(array $uploadedFiles) :void
    {
        $this->files = $uploadedFiles;
    }

    public function withoutAttribute($name) :void
    {
        unset($this->attributes[(string) $name]);
    }

    public function isKeepAlive() :bool
    {
        return (float) $this->getProtocolVersion() >= 1.1 && strtolower($this->getHeader('Connection')) == 'keep-alive';
    }

}
