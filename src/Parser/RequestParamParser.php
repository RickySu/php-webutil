<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestParamParser extends BaseParser
{
    protected $rawData;
    protected $parsed = false;
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

    protected function parse()
    {
        $header = $this->parseData['header'];

        if(!isset($header['content-length'])){
            $this->forwardHook($this->rawData);
            $this->setParsed();
            return;
        }

        if(strlen($this->rawData) > $header['content-length']){
            throw new Exception\ParserException('content too large.', Exception\ParserException::CONTENT_TOO_LARGE);
        }

        if(($contentType = $this->parseContentType($header['content-type'])) === false){
            return;
        }

        if($this->parseContent($contentType[0], $contentType[1], $header['content-length'])){
            return;
        }

        $this->forwardHook($this->rawData);
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
            case 'multipart/form-data':
                $this->parseData['content-boundary'] = $extraDatasParsed['boundary'];
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