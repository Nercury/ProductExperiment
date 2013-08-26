<?php
/**
 * @author nerijus
 */

namespace Evispa\Component\MultipartResource\Exception;

class ResourceNotFoundException extends \Exception
{
    public function __construct($message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
