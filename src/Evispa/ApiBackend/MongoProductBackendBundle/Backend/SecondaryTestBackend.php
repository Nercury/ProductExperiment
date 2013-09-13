<?php

namespace Evispa\ApiBackend\MongoProductBackendBundle\Backend;
use Evispa\Api\Product\Model\Text\TextV1;
use Evispa\ResourceApiBundle\Backend\SecondaryBackendInterface;

/**
 * Description of SecondaryTestBackend
 *
 * @author nerijus
 */
class SecondaryTestBackend implements SecondaryBackendInterface
{

    public function fetchBySlugs(array $slugs, array $requestedParts)
    {
        $results = array();
        foreach ($slugs as $slug) {
            $text = new TextV1();
            $text->name = 'dfsdf';
            $results[$slug]['product.text'] = $text;
        }

        return $results;
    }

    public function fetchOne($slug, array $requestedParts)
    {

    }

    public function getNew(array $requestedParts)
    {
        $result = array();

        if (in_array('product.text', $requestedParts)) {
            $text = new TextV1();
            $text->name = 'Super Tekstas';
            $result['product.text'] = $text;
        }

        return $result;
    }
}