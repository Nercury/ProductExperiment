<?php
/**
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\Api\Product\Model\Text;

use JMS\Serializer\Annotation\Type;
use Evispa\ObjectMigration\Annotations as Api;

/**
 * @Api\Version("vnd.evispa.product.text.v1")
 */
class TextV1
{
    /**
     * Product name.
     *
     * @Type("string")
     *
     * @var string $name
     */
    public $name;

    /**
     * Product short description.
     *
     * Usually seen as addition to name, a summary.
     *
     * @Type("string")
     *
     * @var string $shortDescription
     */
    public $shortDescription;

    /**
     * Product full description.
     *
     * Full information about the product.
     *
     * @Type("string")
     *
     * @var string $description
     */
    public $description;
}