<?php
namespace WebUtil\Parser;

interface ParserInterface
{
    public function feed($data);
    public function setOnParsedCallback($callback);
    public function setNextHook(ParserInterface $nextHook);
    public function initHook(ParserInterface $prevHook);
}
