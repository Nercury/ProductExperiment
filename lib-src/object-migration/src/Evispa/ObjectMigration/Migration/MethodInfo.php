<?php

namespace Evispa\ObjectMigration\Migration;

/**
 * Description of MethodInfo
 *
 * @author nerijus
 */
class MethodInfo
{
    /**
     *
     * @var \ReflectionMethod
     */
    public $method;

    /**
     *
     * @var \Evispa\ObjectMigration\Annotations\Migration
     */
    public $annotation;

    public function __construct(\ReflectionMethod $method, \Evispa\ObjectMigration\Annotations\Migration $annotation)
    {
        $this->method = $method;
        $this->annotation = $annotation;
    }
}