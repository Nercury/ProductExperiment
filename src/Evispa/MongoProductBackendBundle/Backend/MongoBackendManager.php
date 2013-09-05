<?php

namespace Evispa\MongoProductBackendBundle\Backend;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Evispa\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\BackendManagerInterface;

/**
 * @author nerijus
 */
class MongoBackendManager implements BackendManagerInterface
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

        return $product;

    }
}