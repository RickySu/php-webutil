<?php
namespace WebUtil\Route;

class RouteFactory
{
    private function __construct()
    {
    }

    /**
     *
     * @param strig $class
     * @return RouteInterface
     */
    static public function create($class = 'FastRoute')
    {
        $className = '\\WebUtil\\Route\\'.$class;
        return new $className();
    }
}