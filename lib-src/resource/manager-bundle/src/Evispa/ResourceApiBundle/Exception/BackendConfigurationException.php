<?php

namespace Evispa\ResourceApiBundle\Exception;

/**
 * @author nerijus
 */
class BackendConfigurationException extends \LogicException
{
    function __construct($message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}