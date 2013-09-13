<?php

namespace Evispa\ProductApiBundle\Controller;

//use Evispa\Resource\Component\MultipartResource\Annotations\Resource;

use Evispa\ObjectMigration\VersionConverter;
use Evispa\ProductApiBundle\Rest\ProductData;
use Evispa\ResourceApiBundle\Backend\FindParameters;
use Evispa\ResourceApiBundle\Manager\ResourceManager;
use Evispa\ResourceApiBundle\VersionParser\AcceptVersionParser;
use Evispa\ResourceApiBundle\VersionParser\VersionAndFormat;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View as RestView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ProductsController extends Controller
{
    /**
     * Get product resource manager.
     *
     * @param array $options
     * @return ResourceManager
     */
    private function getProductResourceManager($options)
    {
        $prm = $this->get('resource_managers')->getResourceManager('product', $options);
        return $prm;
    }

    /**
     * Get version converter for Product resource.
     *
     * @param ResourceManager $prm Resource manager.
     *
     * @return VersionConverter
     */
    private function getProductVersionConverter($prm)
    {
        return new VersionConverter(
            $prm->getVersionReader(),
            $prm->getClassName(),
            $prm->getConverterOptions()
        );
    }

    /**
     * Get version and format for the request.
     *
     * @param Request $request
     * @param ResourceManager $prm
     *
     * @return VersionAndFormat
     */
    private function getExpectedVersionAndFormat($request, ResourceManager $prm)
    {
        $versionReader = $prm->getVersionReader();
        $restVersionParser = new AcceptVersionParser();
        return $restVersionParser
            ->setAllowedVersions($versionReader->getAllowedClassOutputVersions($prm->getClassName()))
            ->setRequestedFormat($request->getRequestFormat())
            ->setDefault('html', $versionReader->getClassVersion('Evispa\Api\Product\Model\ProductV1'))
            ->setDefault('json', $versionReader->getClassVersion('Evispa\Api\Product\Model\SimpleProductV1'))
            ->setDefault('xml', $versionReader->getClassVersion('Evispa\Api\Product\Model\SimpleProductV1'))
            ->parseVersionAndFormat($request->getAcceptableContentTypes());
    }

    /**
     * Get a product by its identifier.
     *
     * @ApiDoc
     * @View(templateVar="product")
     */
    public function getProductAction(ParamFetcher $paramFetcher, $slug)
    {
        $request = $this->getRequest();

        $options = array('locale' => $request->getLocale());

        $prm = $this->getProductResourceManager($options);
        $product = $prm->fetchOne(
            $slug
        );

        if (null === $product) {
            throw new NotFoundHttpException("Product was not found.");
        } else {
            $view = RestView::create();

            // Find out what client wants. Impossible, but very important.
            $expectedVersionAndFormat = $this->getExpectedVersionAndFormat($request, $prm);

            if (null === $expectedVersionAndFormat) {
                throw new NotFoundHttpException(
                    'Can not return requested resource in ' . $request->getRequestFormat() . ' format.'
                );
            }

            $view->setFormat($expectedVersionAndFormat->getFormat());

            $vc = $this->getProductVersionConverter($prm);
            $product = $vc->migrateToVersion($product, $expectedVersionAndFormat->getVersion());

            $view->setData($product);
        }

        return $view;
    }

    /**
     * Get list of all products.
     *
     * @ApiDoc
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the product list.")
     * @QueryParam(name="count", requirements="\d+", default="50", strict=true, nullable=true, description="Item count limit")
     * @View(templateVar="result")
     */
    public function getProductsAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');

        $request = $this->getRequest();

        $options = array('locale' => $request->getLocale());
        $prm = $this->getProductResourceManager($options);

        $params = new FindParameters();
        $params->limit = 5;
        $params->offset = ($page - 1) * $params->limit;

        $resourcesObject = $prm->fetchAll($params);

        $view = RestView::create();

        // Find out what client wants. Impossible, but very important.
        $expectedVersionAndFormat = $this->getExpectedVersionAndFormat($request, $prm);
        if (null === $expectedVersionAndFormat) {
            throw new NotFoundHttpException(
                'Can not return requested resource in ' . $request->getRequestFormat() . ' format.'
            );
        }

        $view->setFormat($expectedVersionAndFormat->getFormat());
        $vc = $this->getProductVersionConverter($prm);

        // TODO: Change "$results" to proper REST object with links to prev/next.

        $results = array('resources' => array());
        $results['total'] = $resourcesObject->getTotalFound();
        $results['parameters'] = $resourcesObject->getParameters();

        foreach ($resourcesObject->getResources() as $resource) {
            $results['resources'][] = $vc->migrateToVersion($resource, $expectedVersionAndFormat->getVersion());
        }

        $view->setData($results);

        return $view;
    }

    /**
     * Create a new product.
     *
     * @ApiDoc
     * @View(templateVar="product")
     */
    public function postProductsAction()
    {
        $request = $this->getRequest();

        $data = array();

        $fb = $this->createFormBuilder(null, array(
            'csrf_protection' => false
        ));
        $fb->add('slug', 'text');
        $fb->add('name', 'text');

        $form = $fb->getForm();
        $form->setData($data);

        //var_dump($product); die;

        $view = RestView::create();

        /*if (false === $request->request->get('form', false)) {
            if ('html' !== $view->getFormat()) {
                $form->addError(new \Symfony\Component\Form\FormError('Submit form data based on specified parameters.'));
            }
        } else {*/
        $form->submit($request);
        $data = $form->getData();
        if ($form->isValid()) {

            return new Response($this->get('serializer')->serialize($data, 'json'));
        }
        //}

        $view->setData($form);
        $view->setStatusCode(400);

        return $view;
    }

    /**
     * Update a product.
     *
     * @ApiDoc
     * @@Resource("product", action="update")
     */
    public function putProductAction(Request $request, $slug)
    {
        $data = new ProductData();
        $data->setSlug($slug);
        $data["name"] = "Title";

        $fb = $this->createFormBuilder(null, array(
            'csrf_protection' => false
        ));
        $fb->add('name', 'text', array(
            'property_path' => '[name]'
        ));

        $form = $fb->getForm();
        $form->setData($data);

        if (false !== $request->request->get('form', false)) {
            $form->submit($request);
            $data = $form->getData();
            if ($form->isValid()) {
                return new Response($this->get('serializer')->serialize($data, 'json'));
            }
        }

        return RestView::create($form, 400);
    }
}
