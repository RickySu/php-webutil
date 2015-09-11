<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestMultipartAsyncParser extends BaseParser
{
    const MULTIPART_FORM_START = 0;
    const MULTIPART_FORM_END = 1;
    const MULTIPART_FORM_DATA = 2;

    protected $rawData;
    protected $parsed = false;
    protected $parseData;
    protected $multipartDataCallback;
    protected $readSize = 0;
    protected $boundarySize;
    protected $boundary;
    protected $boundaryInited = false;
    protected $boundaryheader = '';

    public function setOnMultipartDataCallback($callback)
    {
        $this->multipartDataCallback = $callback;
        return $this;
    }

    public function initHook(ParserInterface $prevHook)
    {
        $this->callback = $prevHook->setOnParsedCallback(function($parseData){
            $this->parseData = $parseData;
            if($this->callback){
                call_user_func($this->callback, $this->parseData);
            }
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
        $this->readSize += strlen($data);
        $this->parse();
    }

    protected function parse()
    {
        if(!isset($this->parseData['Content-Boundary'])){
            return;
        }
        $this->boundary = '--'.$this->parseData['Content-Boundary'];
        $this->boundarySize = strlen($this->boundary);
        $this->flushBufferData();
    }

    protected function parseBoundaryHeader($rawHeader)
    {
        $rawHeader = str_replace("\r\n", ';', $rawHeader);
        foreach(explode(';', $rawHeader) as $column){
            if(preg_match('/(.+?):\s*(.+)/i', $column, $match)){
                $this->boundaryInited[trim($match[1])] = trim($match[2]);
            }
            if(preg_match('/(.+?)\s*=\s*\"(.+)\"/i', $column, $match)){
                $this->boundaryInited[trim($match[1])] = trim($match[2]);
            }

        }
    }

    protected function sendData($data)
    {
        if($this->boundaryInited === false){
            $this->boundaryheader .= $data;
            if(($pos = strpos($this->boundaryheader, "\r\n\r\n"))!==false){
                $header = $this->parseBoundaryHeader(substr($this->boundaryheader, 0, $pos));
                $this->callMultipartDataCallback(self::MULTIPART_FORM_START);
                $data = substr($this->boundaryheader, $pos + 4);
                $this->boundaryheader = '';
            }
        }
        $this->callMultipartDataCallback(self::MULTIPART_FORM_DATA, $data);
    }

    public function callMultipartDataCallback($status, $data = null)
    {
        if($this->multipartDataCallback && $this->boundaryInited){
            call_user_func($this->multipartDataCallback, $this->boundaryInited, $status, $data);
        }
    }

    protected function flushBufferData()
    {
        $bondaryNl = "{$this->boundary}\r\n";
        $endboundary = "{$this->boundary}--";
        $bondaryNlSize = strlen($bondaryNl);
        while(true){
            if(strlen($this->rawData) < $bondaryNlSize){
                return;
            }

            $pos = strpos($this->rawData, $bondaryNl);
            $endPos = strpos($this->rawData, $endboundary);

            if($endPos !== false){
                $this->rawData = substr($this->rawData, 0, $endPos - 2);
                $this->parsed = true;
            }

            if(!$this->parsed){
                if($pos === false){
                    $this->sendData(substr($this->rawData, 0, -$bondaryNlSize));
                    $this->rawData = substr($this->rawData, -$bondaryNlSize);
                    return;
                }

                if($pos > 0){
                    $this->sendData(substr($this->rawData, 0, $pos - 2));
                }
                $this->rawData = substr($this->rawData, $pos + $bondaryNlSize);
            }

            if($this->parsed){
                $this->sendData($this->rawData);
                $this->rawData = null;
            }

            $this->callMultipartDataCallback(self::MULTIPART_FORM_END);
            $this->boundaryInited = false;
        }
    }

}