<?hh
namespace WebUtil\Stream;

use  Psr\Http\Message\StreamInterface;

class SimpleStream implements StreamInterface
{
    protected string $content;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function __toString() :string
    {
        return $this->content;
    }

    public function close() :void
    {

    }

    public function detach() :void
    {
    }

    public function eof() :bool
    {
        return false;
    }

    public function getContents() :string
    {
        return $this->content;
    }

    public function getMetadata(?string $key = null) :?string
    {
        return null;
    }

    public function getSize() :int
    {
        return strlen($this->content);
    }

    public function isReadable() :bool
    {
        return false;
    }

    public function isSeekable() :bool
    {
        return false;
    }

    public function isWritable() :bool
    {
        return false;
    }

    public function read(int $length) :?string
    {
        return null;
    }

    public function rewind() :void
    {
    }

    public function seek(int $offset, int $whence = SEEK_SET) :int
    {
        return -1;
    }

    public function tell() :int
    {
        return -1;
    }

    public function write(string $string) :void
    {
        throw new \RuntimeException("stream is not writable");
    }

}