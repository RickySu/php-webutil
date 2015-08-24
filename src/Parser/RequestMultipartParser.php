<?php
namespace WebUtil\Parser;

use WebUtil\Exception;

class RequestMultipartParser extends BaseParser
{
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
        if(!isset($this->parseData['content-boundary'])){
            return;
        }
        $this->boundary = '--'.$this->parseData['content-boundary'];
        $this->boundarySize = strlen($this->boundary);
        $this->flushBufferData();
    }

    protected function parseBoundaryHeader($rawHeader)
    {
        $rawHeader = str_replace("\r\n", ';', $rawHeader);
        foreach(explode(';', $rawHeader) as $column){
            if(trim($column) == ''){
                continue;
            }
            if(preg_match('/(.+?):\s*(.+)/i', $column, $match)){
                $this->boundaryInited[trim($match[1])] = trim($match[2]);
            }
            if(preg_match('/(.+?)\s*=\s*\"(.+)\"/i', $column, $match)){
                $this->boundaryInited[trim($match[1])] = trim($match[2]);
            }

        }
        //echo "$rawHeader\n";
    }

    protected function sendData($data)
    {
        if($this->boundaryInited === false){
            $this->boundaryheader.=$data;
            if(($pos = strpos($this->boundaryheader, "\r\n\r\n"))!==false){
                $header = $this->parseBoundaryHeader(substr($this->boundaryheader, 0, $pos));
                $data = substr($this->boundaryheader, $pos + 4);
                $this->boundaryheader = '';
            }
        }
        if($this->multipartDataCallback){
            call_user_func($this->multipartDataCallback, $this->boundaryInited, $data);
        }
    }

    protected function flushBufferData()
    {
        while(true){
            if(strlen($this->rawData) < $this->boundarySize){
                return;
            }

            $pos = strpos($this->rawData, $this->boundary);
            if($pos === false){
                $this->sendData(substr($this->rawData, 0, -$this->boundarySize));
                $this->rawData = substr($this->rawData, -$this->boundarySize);
                return;
            }
            if($pos > 0){
                $this->sendData(substr($this->rawData, 0, $pos));
            }
            $this->boundaryInited = false;
            $this->rawData = substr($this->rawData, $pos + $this->boundarySize);
            if(strpos($this->rawData, '--') === 0){
                $this->parsed = true;
                return;
            }
        }
    }

}