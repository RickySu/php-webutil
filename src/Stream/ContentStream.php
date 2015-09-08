<?php
namespace WebUtil\Stream;

use  Psr\Http\Message\StreamInterface;

class ContentStream implements StreamInterface
{
    protected $content;
    private $seekpos = 0;

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
        return $this->seekpos >= strlen($this->content);
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
        return true;
    }

    public function isSeekable()
    {
        return true;
    }

    public function isWritable()
    {
        return false;
    }

    public function read($length)
    {
        if($this->eof()){
            return null;
        }

        if($this->seekpos + $length > $this->getSize()){
            $length = $this->getSize() - $this->seekpos;
        }

        $read = substr($this->content, $this->seekpos, $length);
        $this->seekpos+=$length;
        return $read;
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        switch($whence){
            case SEEK_SET:
                $this->seekpos = $offset;
                break;
            case SEEK_CUR:
            default:
                $this->seekpos+=$offset;
                if($this->eof()){
                    $this->seekpos = strlen($this->content) - 1;
                }
        }

        return $this->seekpos;
    }

    public function tell()
    {
        return $this->seekpos;
    }

    public function write($string)
    {
        throw new \RuntimeException("stream is not writable");
    }

}