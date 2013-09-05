<?php

namespace Evispa\MongoProductBackendBundle\Backend;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Evispa\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\BackendInterface;

/**
 * @author nerijus
 */
class MongoBackendManager implements BackendInterface
{
    /** @var ManagerRegistry */
    protected $mongodb;

    public function __construct($mongodb)
    {
        $this->mongodb = $mongodb;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function getManager()
    {
        return $this->mongodb->getManager();
    }

    public function findOne($slug, array $requestedParts)
    {
        /** @var Product $product */
        $product = $this->mongodb->getRepository('EvispaMongoProductBackendBundle:Product')->find($slug);
//
//        $product->getCode();
//        $product->getText();

        $result = array();

        if (in_array('product.code', $requestedParts)) {
            $result['product.code'] = new \Evispa\Api\Product\Model\Code\CodeV1();
            $result['product.code']->code = "X";
        }

        if (in_array('product.text', $requestedParts)) {
            $result['product.text'] = new \Evispa\Api\Product\Model\Text\TextV1();
            $result['product.text']->name = "Pav Mongo Yay";
        }

        return $result;

    }
}