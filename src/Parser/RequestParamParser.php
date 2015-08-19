<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestParamParser extends BaseParser
{
    protected $rawData;
    protected $parsed = false;
    protected $callback;
    protected $parseData;

    public function initHook(ParserInterface $prevHook)
    {
        $this->callback = $prevHook->setOnParsedCallback(function($parseData){
            $this->parseData = $parseData;
            $this->parse();
        });
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

    protected function parseSemicolonField($data)
    {
        $return = [];
        foreach(explode($data, ':') as $field){
            list($key, $val) = explode($field, '=');
            $return[trim($key)] = trim($val);
        }
        return $return;
    }

    protected function parse()
    {
        if(!is_array($this->parseData)){
            return;
        }

        $header = $this->parseData['header'];
        if(isset($header['content-length'])){
            if(strlen($this->rawData) > $header['content-length']){
                throw new Exception\ParserException('content too large.');
            }
        }

        if(($contentType = $this->parseContentType($header['content-type'])) === false){
            return;
        }

        if($this->parseContent($match[0], isset($match[1])?$match[1]:null, $header['content-length'])){
            return;
        }

        $this->forwardHook($this->parseData);
        $this->setParsed();
    }

    protected function setParsed()
    {
        if($this->callback){
            call_user_func($this->callback, $this->parseData);
        }
        $this->parsed = true;
        unset($this->parseData);
    }

    protected function parseContentType($contentType)
    {
        if(!preg_match('/^(.*);?(.*)/i', $contentType, $match)){
            return false;
        }

        return array($match[0], $match[1]);
    }

    protected function parseContent($type, $extraDatas, $contentLength)
    {
        $rawDataLength = strlen($this->rawData);
        switch($type){
            case 'application/x-www-form-urlencoded':
                if($contentLength == $rawDataLength){
                    $this->parseData['content'] = $this->rawData;
                    $this->parseData['content-parsed'] = $this->parseUrlEncode($this->rawData);
                    $this->setParsed();
                }
                return true;
            case 'application/json':
                if($contentLength == $rawDataLength){
                    $this->parseData['content'] = $this->rawData;
                    $this->parseData['content-parsed'] = $this->parseJSONEncode($this->rawData);
                    $this->setParsed();
                }
                return true;
        }
        return false;
    }

    protected function parseJSONEncode($data)
    {
        return json_decode($data, true);
    }

    protected function parseUrlEncode($data)
    {
        parse_str($data, $parsed);
        return $parsed;
    }
}