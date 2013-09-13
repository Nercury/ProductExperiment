<?php

namespace Evispa\ProductApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author nerijus
 */
class ProductEditController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * @return \Evispa\ResourceApiBundle\Manager\ResourceManager
     */
    private function getProductManager() {
        return $this->get('resource_api.product');
    }

    /**
    * @Route("/product/create")
    */
    public function createAction() {
        $request = $this->getRequest();

        $rm = $this->getProductManager();

        $options = array(
            'locale' => 'en',
        );

        $product = $rm->getNew($options);

        $simpleProduct = $product;

        foreach ($rm->migrationInfo->getOutputMigrationActions('Evispa\Api\Product\Model\SimpleProductV1') as $action) {
            $simpleProduct = $action->run($simpleProduct, $options);
        }

        $formBuilder = $this->createFormBuilder($simpleProduct);
        $formBuilder->add('code', 'text');
        $formBuilder->add('name', 'text', array(
            'property_path' => 'text.name',
        ));

        $form = $formBuilder->getForm();
        if ($request->isMethod('POST')) {
            $form->submit($request);

            $product = $simpleProduct;

            foreach ($rm->migrationInfo->getInputMigrationActions('Evispa\Api\Product\Model\SimpleProductV1') as $action) {
                $product = $action->run($product, $options);
            }

            $rm->saveOne($product);
        }

        return $this->render('EvispaProductApiBundle:Edit:create.html.twig', array(
            'form' => $form->createView()
        ));
    }
}