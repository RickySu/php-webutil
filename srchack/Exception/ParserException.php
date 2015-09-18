<?php
namespace WebUtil\Exception;

class ParserException extends \Exception
{
    const HEADER_TOO_LARGE = 0;
    const CONTENT_TOO_LARGE = 1;
}

