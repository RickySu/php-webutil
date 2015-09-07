<?php
namespace WebUtil\Route;
use WebUtil\R3 as BaseR3;

class R3 implements RouteInterface
{
    private $r3;

    protected $methods;

    protected $compiled = false;

    public function __construct()
    {
        $this->r3 = new BaseR3();
        $this->methods = array(
            self::METHOD_GET => BaseR3::METHOD_GET,
            self::METHOD_POST => BaseR3::METHOD_POST,
            self::METHOD_PUT => BaseR3::METHOD_PUT,
            self::METHOD_DELETE => BaseR3::METHOD_DELETE,
        );
    }

    public function addRoute($method, $pattern, $data)
    {
        if(!is_array($method)){
            $method = [$method];
        }
        $methods = 0;
        foreach ($method as $m){
            $methods|=$this->methods[$m];
        }
        $this->r3->addRoute($pattern, $methods, $data);
        $this->compiled = false;
    }

    public function match($method, $uri)
    {
        return $this->r3->match($uri, $this->methods[$method]);
    }

    public function compile()
    {
        if(!$this->compiled){
            $this->r3->compile();
            $this->compiled = true;
        }
    }

}
