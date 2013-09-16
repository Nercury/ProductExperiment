<?php
/**
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\ResourceApiBundle\Exception;

/**
 * Thrown on invalid resource request, usually caused by coding logic error.
 */
class ResourceRequestException extends \LogicException
{
    public function __construct($message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
