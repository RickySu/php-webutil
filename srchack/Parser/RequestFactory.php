<?php
namespace WebUtil\Parser;

class RequestFactory
{
    private function __construct()
    {
    }

    /**
     *
     * @param strig $class
     * @return RouteInterface
     */
    static public function create($class = 'HttpParser')
    {
        $className = '\\WebUtil\\Parser\\'.$class;
        if(class_exists($className)){
            return new $className();
        }

        $parser = new RequestHeaderParser();
        $parser
            ->setNextHook(new RequestParamParser())
            ->setNextHook(new RequestMultipartAsyncParser());
        return $parser;
    }
}