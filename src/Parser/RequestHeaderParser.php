<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestHeaderParser extends BaseParser
{
    const MAX_HEADER_SIZE = 8192; // header limit 8K

    protected $rawData;
    protected $parsed = false;

    public function initHook(ParserInterface $prevHook)
    {

    }

    protected function forwardHook($data)
    {
        if($this->nextHook){
            call_user_func($this->nextHook, $data);
        }
    }

    public function feed($data)
    {
        if($this->parsed){
            $this->forwardHook($data);
            return;
        }

        $this->rawData .= $data;
        $this->parse();
    }

    protected function parse()
    {

        if(strlen($this->rawData) > static::MAX_HEADER_SIZE){
            throw new Exception\ParserException('header too large.');
        }

        if($this->spliteHeader($rawHeaders, $data) === false){
            return false;
        }

        unset($this->rawData);

        if($data !== false){
            $this->forwardHook($data);
        }

        if($this->callback){
            call_user_func($this->callback, $this->parseHeader($rawHeaders));
        }

        $this->parsed = true;
        return true;
    }

    protected function parseHeader($rawHeaders)
    {
        $headers = [];
        foreach(explode("\r\n", $rawHeaders) as $index => $rawHeader){
            if($index == 0){
                if(preg_match('/^(\w+)\s+(.+)\s+(\w+)\/(\d+\.\d+)$/i', $rawHeader, $match)){
                    $headers['request'] = array(
                        'method' => $match[1],
                        'uri' => $match[2],
                        'protocol' => $match[3],
                        'protocol-version' => $match[4],
                    );
                }
                continue;
            }
            if(($pos = strpos($rawHeader, ':')) === false){
                continue;
            }
            $column = strtolower(trim(substr($rawHeader, 0, $pos)));
            $headers['header'][$column] = trim(substr($rawHeader, $pos+1));
        }
        return $headers;
    }

    protected function spliteHeader(&$rawHeader, &$data)
    {
        if(($pos = strpos($this->rawData, "\r\n\r\n")) != false){
            $rawHeader = substr($this->rawData, 0, $pos);
            $data = substr($this->rawData, $pos + 4);
        }
        return $pos;
    }

}