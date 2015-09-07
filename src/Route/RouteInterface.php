<?php
namespace WebUtil\Route;

interface RouteInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    public function addRoute($method, $pattern, $data);
    public function match($method, $uri);
    public function compile();
}
