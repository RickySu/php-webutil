<?php
namespace WebUtil\Tests;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected function invokeObjectMethod($object, $methodName, $args = array())
    {
        $reflactor = new \ReflectionClass($object);
        $method = $reflactor->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }

    protected function setObjectProperty($object, $propertyName, $value)
    {
        $reflactor = new \ReflectionClass($object);
        $property = $reflactor->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}