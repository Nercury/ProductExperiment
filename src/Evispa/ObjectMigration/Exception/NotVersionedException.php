<?php
/**
 * @author nerijus
 */

namespace Evispa\ObjectMigration\Exception;

class NotVersionedException extends \Exception
{
    public function __construct($className, $code = null, $previous = null)
    {
        parent::__construct(
            'Can not use class "' .
            $className .
            '" as resource, because it does not have Evispa\ObjectMigration\Annotations\Version annotation.',
            $code,
            $previous
        );
    }
}