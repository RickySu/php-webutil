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
    static public function create($class = 'R3')
    {
        $className = '\\WebUtil\\Route\\'.$class;
        try{
            return new $className();
        }
        catch (\Exception $e){
            return self::create('FastRoute');
        }
    }
}