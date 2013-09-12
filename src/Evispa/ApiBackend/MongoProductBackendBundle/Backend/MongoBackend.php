<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Evispa\ApiBackend\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultObject;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendResultsObject;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;

/**
 * @author nerijus
 */
class MongoBackend implements PrimaryBackendInterface
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
     * @param Product $product
     * @param array   $requestedParts
     *
     * @return PrimaryBackendResultObject
     */
    private function createResult(Product $product, array $requestedParts)
    {
        $result = new PrimaryBackendResultObject($product->getSlug());

        if (in_array('product.code', $requestedParts)) {
            $code = new \Evispa\Api\Product\Model\Code\ProductCodeV1();
            $code->code = $product->getCode();

            $result->addPart('product.code', $code);
        }

        if (in_array('product.route', $requestedParts)) {
            $route = new \Evispa\Api\Product\Model\Route\RouteV1();
            if (null !== $product->getRouteSlug()) {
                $route->slug = $product->getRouteSlug();
            }

            $result->addPart('product.route', $route);
        }

        if (in_array('product.text', $requestedParts)) {
            $text = new \Evispa\Api\Product\Model\Text\TextV1();
            $text->name = $product->getText();

            $result->addPart('product.text', $text);
        }

        return $result;
    }

    /**
     * @param string $slug
     * @param array  $requestedParts
     *
     * @return PrimaryBackendResultObject|null
     */
    public function fetchOne($slug, array $requestedParts)
    {
        /** @var Product $product */
        $product = $this->mongodb->getRepository('EvispaMongoProductBackendBundle:Product')->find($slug);

        if (null === $product) {
            return null;
        }

        return $this->createResult($product, $requestedParts);
    }

    /**
     * @param FetchParameters $params
     * @param array          $requestedParts
     *
     * @return PrimaryBackendResultObject[string]
     */
    public function fetchAll(FetchParameters $params, array $requestedParts)
    {
        // just for testing purpose
        $qb = $this->mongodb->getManager()->createQueryBuilder('EvispaMongoProductBackendBundle:Product');
        $adapter = new DoctrineODMMongoDBAdapter($qb);

        $results = new PrimaryBackendResultsObject($adapter->getNbResults());
        $products = $adapter->getSlice($params->offset, $params->limit);

        foreach ($products as $product) {
            $results->addObject($this->createResult($product, $requestedParts));
        }

        return $results;
    }
}
