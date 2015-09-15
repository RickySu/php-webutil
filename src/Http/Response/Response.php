<?php
namespace WebUtil\Http\Response;

use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface
{
    protected $protocolVersion = '1.0';
    protected $headers = [];
    protected $headersString;
    protected $body;
    protected $reasonPhrase;
    protected $statusCode;
    protected $prepared = false;

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
    public static $statusTexts = array(
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

    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = $headers;
        $this->withBodyRaw($content);
        $this->withStatus($status);
    }

    public function getBody()
    {
        return new ContentStream($this->body);
    }

    public function getHeader($name)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    public function getHeaderLine($name)
    {
        $header = isset($this->headers[$name]) ? $this->headers[$name] : null;
        if ($header === null) {
            return null;
        }
        return "$name: $header";
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function withAddedHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function withBodyRaw($body)
    {
        $this->body = $body;
        $this->withAddedHeader('Content-Length', strlen($body));
    }

    public function withBody(\Psr\Http\Message\StreamInterface $body)
    {
        $this->withBodyRaw($body->getContents());
    }

    public function withHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function withProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = $code;
        if($reasonPhrase === ''){
            $this->reasonPhrase = static::$statusTexts[$code];
        }
    }

    public function withoutHeader($name)
    {
        unset($this->headers[$name]);
    }

    public function prepare()
    {
        if(!$this->hasHeader('Content-Type')){
            $this->withAddedHeader('Content-Type', 'text/html; charset=UTF-8');
        }

        foreach($this->headers as $name => $value){
            $this->headersString.="$name: $value\r\n";
        }
        return $this;
    }

    public function __toString()
    {
        return $this->getOutput();
    }

    public function withoutKeepAlive()
    {
        $this->withoutHeader('Connection');
    }

    public function withKeepAlive()
    {
        $this->withProtocolVersion('1.1');
        $this->withHeader('Connection', 'Keep-Alive');
    }

    public function getOutput()
    {
        $this->prepare();
        return
            "HTTP/{$this->protocolVersion} {$this->statusCode} {$this->reasonPhrase}\r\n".
            $this->headersString."\r\n".
            $this->body;
    }
}
