<?php

namespace Evispa\ResourceApiBundle\Exception;

/**
 * @author nerijus
 */
class ObjectMigrationException extends \Exception
{
    public function __construct($message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
