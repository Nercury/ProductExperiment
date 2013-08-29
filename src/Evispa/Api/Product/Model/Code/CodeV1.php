<?php
/**
 * @author nerijus
 */

namespace Evispa\Api\Product\Model\Code;

use JMS\Serializer\Annotation\Type;
use Evispa\ObjectMigration\Annotations as Api;

/**
 * @Api\Version("vnd.evispa.code.v1")
 */
class CodeV1
{
    /**
     * Unique product code for a shop.
     *
     * @Type("string")
     *
     * @var string $code
     */
    public $code;
}