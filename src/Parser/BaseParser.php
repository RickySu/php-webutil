<?php
namespace WebUtil\Parser;

abstract class BaseParser implements ParserInterface
{
    protected $nextHook;
    protected $callback;

    public function setNextHook(ParserInterface $nextHook)
    {
        $this->nextHook = $nextHook;
        $nextHook->initHook($this);
        return $nextHook;
    }

    public function setOnParsedCallback($callback)
    {
        list($this->callback, $callback) = array($callback, $this->callback);
        return $callback;
    }
}