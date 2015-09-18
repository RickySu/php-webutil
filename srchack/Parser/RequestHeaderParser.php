<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestHeaderParser extends BaseParser
{
    const MAX_HEADER_SIZE = 8192; // header limit 8K

    protected $rawData;
    protected $parsed = false;

    public function reset()
    {
        $this->rawData = null;
        $this->parsed = false;
        if($this->nextHook){
            $this->nextHook->reset();
        }
    }

    public function initHook(ParserInterface $prevHook)
    {

    }

    protected function forwardHook($data)
    {
        if($this->nextHook){
            $this->nextHook->feed($data);
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
        if($this->spliteHeader($rawHeaders, $data) === false){
            if(strlen($this->rawData) > static::MAX_HEADER_SIZE){
                throw new Exception\ParserException('header too large.', Exception\ParserException::HEADER_TOO_LARGE);
            }
            return false;
        }

        unset($this->rawData);

        $parsedData = $this->parseCookie($this->parseQuery($this->parseHeader($rawHeaders)));

        $this->parsed = true;

        if($this->callback){
            call_user_func($this->callback, $parsedData);
        }

        if($data !== false){
            $this->forwardHook($data);
        }

        return true;
    }

    protected function parseQuery($parsedData)
    {
        if(($pos = strpos($parsedData['Request']['Target'], '?')) === false){
            $parsedData['Query'] = array(
                'Path' => $parsedData['Request']['Target'],
                'Param' => array(),
            );

            return $parsedData;
        }

        parse_str(substr($parsedData['Request']['Target'], $pos + 1), $result);

        $parsedData['Query'] = array(
            'Path' => substr($parsedData['Request']['Target'], 0, $pos),
            'Param' => $result,
        );
        return $parsedData;
    }

    protected function parseCookie($parsedData)
    {
        if(isset($parsedData['Header']['Cookie'])){
            $parsedData['Header']['Cookie'] = $this->parseSemicolonField($parsedData['Header']['Cookie']);
        }
        return $parsedData;
    }

    protected function parseHeader($rawHeaders)
    {
        $headers = [];
        foreach(explode("\r\n", $rawHeaders) as $index => $rawHeader){
            if($index == 0){
                if(preg_match('/^(\w+)\s+(.+)\s+(\w+)\/(\d+\.\d+)$/i', $rawHeader, $match)){
                    $headers['Request'] = array(
                        'Method' => $match[1],
                        'Target' => $match[2],
                        'Protocol' => $match[3],
                        'Protocol-Version' => $match[4],
                    );
                }
                continue;
            }
            if(($pos = strpos($rawHeader, ':')) === false){
                continue;
            }
            $column = trim(substr($rawHeader, 0, $pos));
            $headers['Header'][$column] = trim(substr($rawHeader, $pos+1));
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