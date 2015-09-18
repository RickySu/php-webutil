<?hh
namespace WebUtil\Http\Response;

use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    protected string $protocolVersion = '1.0';
    protected array<string, string> $headers = [];
    protected string $headersString = '';
    protected string $body = '';
    protected string $reasonPhrase = '';
    protected int $statusCode = 200;
    protected bool $prepared = false;

   /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static array<int, string> $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    public function __construct($content = '', $status = 200, $headers = array()) :void
    {
        $this->headers = (array)$headers;
        $this->withBodyRaw((string)$content);
        $this->withStatus((int)$status);
    }

    public function getBody() :SimpleStream
    {
        return new SimpleStream($this->body);
    }

    public function getHeader($name) :?string
    {
        return isset($this->headers[(string)$name]) ? $this->headers[(string)$name] : null;
    }

    public function getHeaderLine($name) :?string
    {
        $header = isset($this->headers[(string)$name]) ? $this->headers[(string)$name] : null;
        if ($header === null) {
            return null;
        }
        return "$name: $header";
    }

    public function getHeaders() :array<string, string>
    {
        return $this->headers;
    }

    public function getProtocolVersion() :string
    {
        return $this->protocolVersion;
    }

    public function getReasonPhrase() :string
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode() :int
    {
        return $this->statusCode;
    }

    public function hasHeader($name) :bool
    {
        return isset($this->headers[(string)$name]);
    }

    public function withAddedHeader($name, $value) :void
    {
        $this->headers[(string)$name] = (string) $value;
    }

    public function withBodyRaw(string $body) :void
    {
        $this->body = $body;
        $this->withAddedHeader('Content-Length', strlen($body));
    }

    public function withBody(\Psr\Http\Message\StreamInterface $body) :void
    {
        $this->withBodyRaw((string) $body->getContents());
    }

    public function withHeader($name, $value) :void
    {
        $this->headers[(string) $name] = (string) $value;
    }

    public function withProtocolVersion($version) :void
    {
        $this->protocolVersion = (string) $version;
    }

    public function withStatus($code, $reasonPhrase = '') :void
    {
        $this->statusCode = (int) $code;
        if($reasonPhrase === ''){
            $this->reasonPhrase = static::$statusTexts[(int) $code];
        }
    }

    public function withoutHeader($name) :void
    {
        unset($this->headers[(string) $name]);
    }

    public function prepare() :void
    {
        if(!$this->hasHeader('Content-Type')){
            $this->withAddedHeader('Content-Type', 'text/html; charset=UTF-8');
        }

        foreach($this->headers as $name => $value){
            $this->headersString.="$name: $value\r\n";
        }
    }

    public function __toString() :string
    {
        return $this->getOutput();
    }

    public function withoutKeepAlive() :void
    {
        $this->withoutHeader('Connection');
    }

    public function withKeepAlive() :void
    {
        $this->withProtocolVersion('1.1');
        $this->withHeader('Connection', 'Keep-Alive');
    }

    public function getOutput() :string
    {
        $this->prepare();
        return
            "HTTP/{$this->protocolVersion} {$this->statusCode} {$this->reasonPhrase}\r\n".
            $this->headersString."\r\n".
            $this->body;
    }
}
