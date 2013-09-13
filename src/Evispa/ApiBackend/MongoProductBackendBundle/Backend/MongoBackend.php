<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Evispa\Api\Product\Model\Code\ProductCodeV1;
use Evispa\Api\Product\Model\Route\RouteV1;
use Evispa\Api\Product\Model\Text\TextV1;
use Evispa\ApiBackend\MongoProductBackendBundle\Document\Product;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendInterface;
use Evispa\ResourceApiBundle\Backend\FetchParameters;
use Evispa\ResourceApiBundle\Backend\PrimaryBackendObject;
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
     * @return PrimaryBackendObject
     */
    private function createResult(Product $product, array $requestedParts)
    {
        $result = new PrimaryBackendObject($product->getSlug());

        if (in_array('product.code', $requestedParts)) {
            $code = new ProductCodeV1();
            $code->code = $product->getCode();

            $result->setPart('product.code', $code);
        }

        if (in_array('product.route', $requestedParts)) {
            $route = new RouteV1();
            if (null !== $product->getRouteSlug()) {
                $route->slug = $product->getRouteSlug();
            }

            $result->setPart('product.route', $route);
        }

        if (in_array('product.text', $requestedParts)) {
            $text = new TextV1();
            $text->name = $product->getText();

            $result->setPart('product.text', $text);
        }

        return $result;
    }

    /**
     * @param string $slug
     * @param array  $requestedParts
     *
     * @return PrimaryBackendObject|null
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
     * @param array           $requestedParts
     *
     * @return PrimaryBackendObject[string]
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

    public function getNew(array $requestedParts)
    {
        var_dump($requestedParts);

        $mongoProduct = new Product();

        return $this->createResult($mongoProduct, $requestedParts);
    }

    /**
     * @param string[] $slugs
     *
     * @return \Evispa\ApiBackend\MongoProductBackendBundle\Document\Product[]
     */
    private function fetchProducts(array $slugs)
    {
        $result = array();

        /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
        $qb = $this->mongodb->getManager()->createQueryBuilder('EvispaMongoProductBackendBundle:Product');
        $qb->field('slug')->in($slugs);

        /** @var Product[] $products */
        $products = $qb->getQuery()->execute();

        foreach ($products as $product) {
            $result[$product->getSlug()] = $product;
        }

        return $result;
    }

    /**
     * @param PrimaryBackendObject[] $objects
     * @param array                  $parts
     *
     * @return mixed
     */
    public function saveAll(array $objects, array $parts)
    {
        /** @var MongoBackendCyclope[] $cyclopes */
        $cyclopes = array();

        $fetchProducts = array();

        foreach ($objects as $object) {
            if (null !== $object->getResourceSlug()) {
                $fetchProducts[] = $object->getResourceSlug();
            }
        }

        $fetchedProducts = array();

        if (false === empty($fetchedProducts)) {
            $fetchedProducts = $this->fetchProducts($fetchedProducts);
        }

        foreach ($objects as $object) {
            $cyclope = new MongoBackendCyclope($object);

            if ($object->getResourceSlug() !== null) {

                if (isset($fetchedProducts[$object->getResourceSlug()])) {
                    $cyclope->entity = $fetchedProducts[$object->getResourceSlug()];
                } else {
                    $cyclope->entity = new Product();
                }

            } else {
                $cyclope->entity = new Product();
            }

            $cyclopes[] = $cyclope;
        }

        foreach ($cyclopes as $cyclope) {

            if (in_array('product.code', $parts)) {
                /** @var \Evispa\Api\Product\Model\Code\ProductCodeV1 $code */
                $code = $cyclope->backendObject->getPart('product.code');

                if (null !== $code) {
                    $cyclope->entity->setCode($code->code);
                }
            }

            if (in_array('product.route', $parts)) {
                /** @var \Evispa\Api\Product\Model\Route\RouteV1 $route */
                $route = $cyclope->backendObject->getPart('product.route');

                if (null !== $route) {
                    $cyclope->entity->setRouteSlug($route->slug);
                }
            }

            if (in_array('product.text', $parts)) {
                /** @var \Evispa\Api\Product\Model\Text\TextV1 $text */
                $text = $cyclope->backendObject->getPart('product.text');

                if (null !== $text) {
                    $cyclope->entity->setText($text->name);
                }
            }

            if (false === $this->mongodb->getManager()->contains($cyclope->entity)) {
                $this->mongodb->getManager()->persist($cyclope->entity);
            }

        }

        $this->mongodb->getManager()->flush();

        $results = array();

        foreach ($cyclopes as $cyclope) {
            if (null === $cyclope->backendObject->getResourceSlug()) {
                $cyclope->backendObject->setResourceSlug($cyclope->entity->getSlug());
            }

            $results[] = $cyclope->backendObject;
        }

        return $results;
    }

    /**
     * @param PrimaryBackendObject $object
     * @param array                $saveParts
     *
     * @return mixed|void
     */
    public function save(PrimaryBackendObject $object, array $saveParts)
    {
        $objects = $this->saveAll(array($object), $saveParts);

        return end($objects);
    }
}
