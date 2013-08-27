<?php
/**
 * @author nerijus
 */

namespace Evispa\ProductAdminBundle\Rest;

use Evispa\Component\MultipartResource\Data\CombinedData;
use FSC\HateoasBundle\Annotation as Rest;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * @Rest\Relation("self",
 *      href=@Rest\Route(
 *          "get_product",
 *          parameters={"slug"=".slug"}
 *      ),
 *      excludeIf={".slug"=null}
 * )
 * @Rest\Relation("products",
 *      href=@Rest\Route("get_products")
 * )
 *
 * @XmlRoot("product")
 */
class ProductData extends CombinedData
{

}