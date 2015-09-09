<?php
namespace WebUtil\Route;

use FastRoute as BaseFastRoute;

class FastRoute implements RouteInterface
{
    private $routes = [];
    private $dispatcher;

    public function addRoute($method, $pattern, $data)
    {
        $this->routes[] = array($method, $pattern, $data);
        $this->dispatcher = null;
    }

    public function match($method, $uri)
    {
        $match = $this->dispatcher->dispatch($method, $uri);
        if($match[0] == 0){
            return null;
        }
        return array($match[1], $match[2]);
    }

    public function compile()
    {
        if(!$this->dispatcher){
            $this->dispatcher = BaseFastRoute\simpleDispatcher(function(BaseFastRoute\RouteCollector $r){
                foreach($this->routes as $route){
                    $r->addRoute($route[0], $route[1], $route[2]);
                }
            });
        }
    }
}
