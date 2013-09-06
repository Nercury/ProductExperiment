<?php

namespace Evispa\MongoProductBackendBundle\Backend;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Evispa\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;

/**
 * @author nerijus
 */
class MongoBackendManager implements PrimaryBackendInterface
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

    /**
     * @param string $slug
     * @param array  $requestedParts
     *
     * @return PrimaryBackendResultObject|null
     */
    public function findOne($slug, array $requestedParts)
    {
        /** @var Product $product */
        $product = $this->mongodb->getRepository('EvispaMongoProductBackendBundle:Product')->find($slug);

        if (null === $product) {
            return null;
        }

        $result = new PrimaryBackendResultObject($product->getSlug());

        if (in_array('product.code', $requestedParts)) {
            $code = new \Evispa\Api\Product\Model\Code\CodeV1();
            $code->code = $product->getCode();

            $result->addPart('product.code', $code);
        }

        if (in_array('product.text', $requestedParts)) {
            $text = new \Evispa\Api\Product\Model\Text\TextV1();
            $text->name = $product->getText();

            $result->addPart('product.text', $text);
        }

        return $result;
    }

    /**
     * @param FindParameters $params
     * @param array          $requestedParts
     *
     * @return PrimaryBackendResultObject[string]
     */
    public function find(FindParameters $params, array $requestedParts)
    {
        // TODO: Implement find() method.
    }

//    public function findOne($slug, array $requestedParts)
//    {
//        /** @var Product $product */
//        $product = $this->mongodb->getRepository('EvispaMongoProductBackendBundle:Product')->find($slug);
//
//        if (null ===  $product) {
//            return null;
//        }
//
//        $result = array();
//
//        $result['slug'] = $product->getSlug();
//
//        if (in_array('product.code', $requestedParts)) {
//            $result['product.code'] = new \Evispa\Api\Product\Model\Code\CodeV1();
//            $result['product.code']->code = $product->getCode();
//        }
//
//        if (in_array('product.text', $requestedParts)) {
//            $result['product.text'] = new \Evispa\Api\Product\Model\Text\TextV1();
//            $result['product.text']->name = $product->getText();
//        }
//
//        return $result;
//    }
//
//    public function find(FindParameters $params, array $requestedParts)
//    {
//        // TODO: Implement find() method.
//    }

}