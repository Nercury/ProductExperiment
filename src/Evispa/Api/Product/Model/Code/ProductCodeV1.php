<?php
/**
 * @author nerijus
 */

namespace Evispa\Api\Product\Model\Code;

use JMS\Serializer\Annotation\Type;
use Evispa\ObjectMigration\Annotations as Api;

/**
 * @Api\Version("vnd.evispa.product.code.v1")
 */
class ProductCodeV1
{
    /**
     * Unique product code for a shop.
     *
     * @Type("string")
     *
     * @var string $code
     */
    public $code = null;

    /**
     * EAN code.
     *
     * @Type("string")
     *
     * @var string $ean
     */
    public $ean = null;

    /**
     * UPC code.
     *
     * @Type("string")
     *
     * @var string $upc
     */
    public $upc = null;

    /**
     * @Api\Migration(from="Evispa\Api\Product\Model\Code\CodeV1")
     *
     * @param CodeV1 $old Old version of code part.
     *
     * @return self
     */
    public static function fromCodeV1(CodeV1 $other) {
        $code = new self();

        $code->code = $other->code;

        return $code;
    }

    /**
     * @Api\Migration(to="Evispa\Api\Product\Model\Code\CodeV1")
     *
     * @return CodeV1
     */
    public function toCodeV1() {
        $other = new CodeV1();

        $other->code = $this->code;

        return $other;
    }
}