<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestParamParser extends BaseParser
{
    protected $rawData;
    protected $parsed = false;
    protected $parseData;

    public function reset()
    {
        $this->rawData = null;
        $this->parseData = null;
        $this->parsed = false;

        if($this->nextHook){
            $this->nextHook->reset();
        }
    }

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

    protected function parse()
    {
        $header = $this->parseData['Header'];

        if(!isset($header['Content-Length'])){
            $this->forwardHook($this->rawData);
            $this->setParsed();
            return;
        }

        if(strlen($this->rawData) > $header['Content-Length']){
            throw new Exception\ParserException('content too large.', Exception\ParserException::CONTENT_TOO_LARGE);
        }

        if(($contentType = $this->parseContentType($header['Content-Type'])) === false){
            return;
        }

        if($this->parseContent($contentType[0], $contentType[1], $header['Content-Length'])){
            return;
        }

        $this->setParsed();

        $this->forwardHook($this->rawData);
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
        $match = explode(';', $contentType);
        return array($match[0], isset($match[1])?$match[1]:null);
    }

    protected function parseContent($type, $extraDatas, $contentLength)
    {
        parse_str($extraDatas, $extraDatasParsed);
        $rawDataLength = strlen($this->rawData);
        switch($type){
            case 'application/x-www-form-urlencoded':
                if($contentLength == $rawDataLength){
                    $this->parseData['Content'] = $this->rawData;
                    $this->parseData['Content-Parsed'] = $this->parseUrlEncode($this->rawData);
                    $this->setParsed();
                }
                return true;
            case 'application/json':
                if($contentLength == $rawDataLength){
                    $this->parseData['Content'] = $this->rawData;
                    $this->parseData['Content-Parsed'] = $this->parseJSONEncode($this->rawData);
                    $this->setParsed();
                }
                return true;
            case 'multipart/form-data':
                $this->parseData['Content-Boundary'] = $extraDatasParsed['boundary'];
                $this->forwardHook($this->rawData);
                $this->setParsed();
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