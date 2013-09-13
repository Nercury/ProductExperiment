<?php

namespace Evispa\ResourceApiBundle\Exception;

/**
 * @author nerijus
 */
class ObjectMigrationException extends \Exception
{
    function __construct($message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}