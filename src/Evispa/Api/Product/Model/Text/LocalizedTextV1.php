<?php
/**
 * @author nerijus
 */

namespace Evispa\Api\Product\Model\Text;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlMap;
use JMS\Serializer\Annotation\XmlKeyValuePairs;

use Evispa\ObjectMigration\Annotations as Api;

/**
 * @Api\Version("vnd.evispa.product.localized.text.v1")
 */
class LocalizedTextV1
{
    /**
     * Array of localized text objects.
     *
     * Array key is the locale string.
     *
     * @Type("array<string, Evispa\Api\Product\Model\Text\TextV1>")
     * @XmlMap
     * @XmlKeyValuePairs
     *
     * @var array
     */
    public $items = array();
}