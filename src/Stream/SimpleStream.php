<?php
namespace WebUtil\Stream;

use  Psr\Http\Message\StreamInterface;

class SimpleStream implements StreamInterface
{
    protected $content;

    public function __construct($content = '')
    {
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function close()
    {

    }

    public function detach()
    {
    }

    public function eof()
    {
        return false;
    }

    public function getContents()
    {
        return $this->content;
    }

    public function getMetadata($key = null)
    {
        return null;
    }

    public function getSize()
    {
        return strlen($this->content);
    }

    public function isReadable()
    {
        return false;
    }

    public function isSeekable()
    {
        return false;
    }

    public function isWritable()
    {
        return false;
    }

    public function read(int $length)
    {
        return null;
    }

    public function rewind()
    {
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        return -1;
    }

    public function tell()
    {
        return -1;
    }

    public function write($string)
    {
        throw new \RuntimeException("stream is not writable");
    }

}